<?php

namespace App\Jobs;

use App\Services\RevenueManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendTotalRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * The job unique ID for tracking progress.
     *
     * @var string
     */
    private string $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->jobId = uniqid('revenue_report_', true);
    }

    /**
     * Execute the job.
     *
     * @throws RequestException
     */
    public function handle(): void
    {
        // Check if we're within hourly quota
        if ($this->hasExceededHourlyQuota()) {
            Log::warning('Hourly quota exceeded for revenue reporting', ['job_id' => $this->jobId]);
            $this->fail(new \Exception('Hourly quota exceeded'));
            return;
        }

        try {
            // Step 1: Verification
            $verificationResponse = $this->postVerification();
            $this->updateProgress('verification_completed', $verificationResponse);

            // Step 2: Report submission
            $reportResponse = $this->postReport($verificationResponse);
            $this->updateProgress('report_submitted', $reportResponse);

            // Step 3: Report confirmation
            $this->postReportConfirmation($reportResponse);
            $this->updateProgress('report_confirmed', null);

            // Mark job as completed
            $this->markJobCompleted();

        } catch (RequestException $e) {
            Log::error('HTTP request failed in revenue report job', [
                'job_id' => $this->jobId,
                'step' => $this->getCurrentStep(),
                'error' => $e->getMessage(),
                'response' => $e->response?->body()
            ]);

            // Don't retry if it's a client error (4xx)
            if ($e->response && $e->response->status() >= 400 && $e->response->status() < 500) {
                $this->fail($e);
                return;
            }

            // For other errors, release the job back to queue
            $this->release($this->backoff);
        } catch (\Exception $e) {
            Log::error('Unexpected error in revenue report job', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage()
            ]);
            $this->fail($e);
        }
    }

    /**
     * Check if we've exceeded the hourly quota.
     *
     * @return bool
     */
    private function hasExceededHourlyQuota(): bool
    {
        $hourlyKey = 'revenue_report_hourly_' . now()->format('Y-m-d-H');
        $count = Cache::get($hourlyKey, 0);
        
        return $count >= 10; // Assuming 10 reports per hour limit
    }

    /**
     * Increment the hourly quota counter.
     *
     * @return void
     */
    private function incrementHourlyQuota(): void
    {
        $hourlyKey = 'revenue_report_hourly_' . now()->format('Y-m-d-H');
        Cache::put($hourlyKey, Cache::get($hourlyKey, 0) + 1, 3600);
    }

    /**
     * Get the current step of the job execution.
     *
     * @return string
     */
    private function getCurrentStep(): string
    {
        $progress = Cache::get("revenue_report_progress_{$this->jobId}", []);
        return end($progress)['step'] ?? 'not_started';
    }

    /**
     * Update the progress of the job execution.
     *
     * @param string $step
     * @param array|null $data
     * @return void
     */
    private function updateProgress(string $step, ?array $data): void
    {
        $progress = Cache::get("revenue_report_progress_{$this->jobId}", []);
        $progress[] = [
            'step' => $step,
            'timestamp' => now()->toISOString(),
            'data' => $data
        ];
        
        Cache::put("revenue_report_progress_{$this->jobId}", $progress, 3600);
    }

    /**
     * Mark the job as completed and clean up.
     *
     * @return void
     */
    private function markJobCompleted(): void
    {
        $this->incrementHourlyQuota();
        Cache::forget("revenue_report_progress_{$this->jobId}");
        Log::info('Revenue report job completed successfully', ['job_id' => $this->jobId]);
    }

    /**
     * Perform HTTP POST to verification endpoint.
     *
     * @throws RequestException
     */
    private function postVerification(): array
    {
        // Check if verification was already completed
        $progress = Cache::get("revenue_report_progress_{$this->jobId}", []);
        $verificationStep = collect($progress)->firstWhere('step', 'verification_completed');
        
        if ($verificationStep) {
            Log::info('Verification step already completed, skipping', ['job_id' => $this->jobId]);
            return $verificationStep['data'];
        }

        return Http::timeout(30)
            ->retry(2, 1000)
            ->post('https://revenue-verifier.com')
            ->throw()
            ->json();
    }

    /**
     * Perform HTTP POST to report endpoint.
     *
     * @param array $verificationResponse
     * @return array
     * @throws RequestException
     */
    private function postReport(array $verificationResponse): array
    {
        // Check if report was already submitted
        $progress = Cache::get("revenue_report_progress_{$this->jobId}", []);
        $reportStep = collect($progress)->firstWhere('step', 'report_submitted');
        
        if ($reportStep) {
            Log::info('Report step already completed, skipping', ['job_id' => $this->jobId]);
            return $reportStep['data'];
        }

        return Http::timeout(30)
            ->retry(2, 1000)
            ->post('https://revenue-reporting.com/reports', [
                'verification_id' => $verificationResponse['id'],
                'total_revenue' => RevenueManager::calculateTotalRevenue(),
            ])
            ->throw()
            ->json();
    }

    /**
     * Perform HTTP POST to report confirmation endpoint.
     *
     * @param array $reportResponse
     * @return array
     * @throws RequestException
     */
    private function postReportConfirmation(array $reportResponse): array
    {
        // Check if confirmation was already sent
        $progress = Cache::get("revenue_report_progress_{$this->jobId}", []);
        $confirmationStep = collect($progress)->firstWhere('step', 'report_confirmed');
        
        if ($confirmationStep) {
            Log::info('Confirmation step already completed, skipping', ['job_id' => $this->jobId]);
            return $confirmationStep['data'] ?? [];
        }

        return Http::timeout(30)
            ->retry(2, 1000)
            ->post('https://revenue-reporting.com/reports/confirm', [
                'report_id' => $reportResponse['id'],
                'timestamp' => now()->timestamp,
            ])
            ->throw()
            ->json();
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Revenue report job failed permanently', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Clean up progress cache
        Cache::forget("revenue_report_progress_{$this->jobId}");
    }
}
