<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Factories\PaymentGatewayFactory;
use App\Strategies\Payments\PayPalPaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return PaymentGatewayFactory::pay(request()->get('payment_method'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
