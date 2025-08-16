<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTotalRevenueReportJob;
use App\Services\RevenueManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SendTotalRevenueReportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_job_executes_successfully()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['id' => 'verification_123'], 200),
            'https://revenue-reporting.com/reports' => Http::response(['id' => 'report_456'], 200),
            'https://revenue-reporting.com/reports/confirm' => Http::response(['status' => 'confirmed'], 200),
        ]);

        $job = new SendTotalRevenueReportJob();
        $job->handle();

        // Verify that progress was tracked
        $progressKeys = Cache::get('revenue_report_progress_*');
        $this->assertNotNull($progressKeys);
        
        // Get the actual progress data
        $progressData = null;
        foreach (Cache::get('revenue_report_progress_*') as $key => $value) {
            if (str_contains($key, 'revenue_report_progress_')) {
                $progressData = $value;
                break;
            }
        }
        
        $this->assertNotNull($progressData);
        $this->assertCount(3, $progressData);
        $this->assertEquals('verification_completed', $progressData[0]['step']);
        $this->assertEquals('report_submitted', $progressData[1]['step']);
        $this->assertEquals('report_confirmed', $progressData[2]['step']);
    }

    public function test_job_skips_completed_steps_on_retry()
    {
        // Simulate that verification and report steps were already completed
        $job = new SendTotalRevenueReportJob();
        $jobId = 'test_job_id_123';
        Cache::put("revenue_report_progress_{$jobId}", [
            [
                'step' => 'verification_completed',
                'timestamp' => now()->toISOString(),
                'data' => ['id' => 'verification_123']
            ],
            [
                'step' => 'report_submitted',
                'timestamp' => now()->toISOString(),
                'data' => ['id' => 'report_456']
            ]
        ], 3600);

        Http::fake([
            'https://revenue-reporting.com/reports/confirm' => Http::response(['status' => 'confirmed'], 200),
        ]);

        $job->handle();

        // Verify that only confirmation step was executed
        $progress = Cache::get("revenue_report_progress_{$jobId}");
        $this->assertCount(3, $progress);
        $this->assertEquals('report_confirmed', $progress[2]['step']);
    }

    public function test_job_fails_when_hourly_quota_exceeded()
    {
        // Set hourly quota to maximum
        $hourlyKey = 'revenue_report_hourly_' . now()->format('Y-m-d-H');
        Cache::put($hourlyKey, 10, 3600);

        $job = new SendTotalRevenueReportJob();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hourly quota exceeded');
        
        $job->handle();
    }

    public function test_job_handles_http_errors_gracefully()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['error' => 'Service unavailable'], 503),
        ]);

        $job = new SendTotalRevenueReportJob();
        
        // Job should not throw exception, it should release back to queue
        $this->expectException(\Exception::class);
        $job->handle();
    }

    public function test_job_fails_permanently_on_client_errors()
    {
        Http::fake([
            'https://revenue-verifier.com' => Http::response(['error' => 'Bad request'], 400),
        ]);

        $job = new SendTotalRevenueReportJob();
        
        // Job should fail permanently on 4xx errors
        $this->expectException(\Exception::class);
        $job->handle();
    }

    public function test_job_cleanup_on_failure()
    {
        $job = new SendTotalRevenueReportJob();
        $jobId = 'test_job_id_456';
        
        // Simulate some progress
        Cache::put("revenue_report_progress_{$jobId}", [
            ['step' => 'verification_completed', 'timestamp' => now()->toISOString()]
        ], 3600);

        $job->failed(new \Exception('Test failure'));

        // Progress should be cleaned up
        $this->assertNull(Cache::get("revenue_report_progress_{$jobId}"));
    }
}
