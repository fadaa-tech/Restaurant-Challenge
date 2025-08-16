<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTotalRevenueReportJob;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendTotalRevenueReportJobTest extends TestCase
{
   #[Test]
public function test_handle()
{
    Http::fakeSequence()
        ->push(['id' => 123], 200) // verification
        ->push(['id' => 456], 200) // report
        ->push(['id' => 789], 200); // confirm

    $job = new SendTotalRevenueReportJob();
    $job->handle();

    Http::assertSentCount(3);

    Http::assertSent(fn($request) => $request->url() === 'https://revenue-verifier.com');
    Http::assertSent(fn($request) => $request->url() === 'https://revenue-reporting.com/reports');
    Http::assertSent(fn($request) => $request->url() === 'https://revenue-reporting.com/reports/confirm');

    $this->assertDatabaseHas('daily_reports', [
        'status' => 'confirmed',
    ]);
}

}
