<?php

namespace App\Jobs;

use App\Services\RevenueManager;
use Illuminate\Bus\Queueable;
use App\Models\DailyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendTotalRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 60; // دقيقة

    public function handle(): void
    {
        $report = DailyReport::firstOrCreate(['date' => today()], [
            'status' => 'pending',
        ]);

        try {
            while (in_array($report->status, ['pending', 'verified', 'reported'])) {

                if ($report->status === 'pending') {
                    $verificationResponse = $this->postVerification();
                    $report->update([
                        'verification_response' => $verificationResponse,
                        'total_revenue' => RevenueManager::calculateTotalRevenue(),
                        'status' => 'verified',
                    ]);
                }

                if ($report->status === 'verified') {
                    $reportResponse = $this->postReport($report->verification_response, $report->total_revenue);
                    $report->update([
                        'report_response' => $reportResponse,
                        'status' => 'reported',
                    ]);
                }

                if ($report->status === 'reported') {
                    $confirmationResponse = $this->postReportConfirmation($report->report_response);
                    $report->update([
                        'confirmation_response' => $confirmationResponse,
                        'status' => 'confirmed',
                    ]);
                }

                $report->refresh();
            }

        } catch (\Throwable $e) {
            if ($this->shouldRetry($e)) {
                $this->release($this->backoff);
            } else {
                $report->update(['status' => 'failed']);
                $this->fail($e);
            }
        }
    }

    private function postVerification(): array
    {
        return Http::withHeaders([
            'Idempotency-Key' => 'verification-' . today()->toDateString(),
        ])->post('https://revenue-verifier.com')->throw()->json();
    }

    private function postReport(array $verificationResponse, float $totalRevenue): array
    {
        return Http::withHeaders([
            'Idempotency-Key' => 'report-' . today()->toDateString(),
        ])->post('https://revenue-reporting.com/reports', [
            'verification_id' => $verificationResponse['id'],
            'total_revenue' => $totalRevenue,
        ])->throw()->json();
    }

    private function postReportConfirmation(array $reportResponse): array
    {
        return Http::withHeaders([
            'Idempotency-Key' => 'confirmation-' . today()->toDateString(),
        ])->post('https://revenue-reporting.com/reports/confirm', [
            'report_id' => $reportResponse['id'],
            'timestamp' => now()->timestamp,
        ])->throw()->json();
    }

    private function shouldRetry(\Throwable $e): bool
    {
        $status = method_exists($e, 'getCode') ? $e->getCode() : 0;
        return $status >= 500 || $status === 0;
    }
}
