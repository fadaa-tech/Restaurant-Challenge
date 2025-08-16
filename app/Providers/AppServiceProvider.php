<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('branch_orders', function (Request $request) {
            $branchId = $request->user()->branch_id
                ?? $request->header('X-Branch-Id')
                ?? $request->get('branch_id');
            $key = $branchId ? "branch:{$branchId}" : $request->ip();

            return Limit::perMinute(60)->by($key);
        });
    }
}
