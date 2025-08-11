<?php

namespace App\Providers;

use App\Events\CustomerOrderNotification;
use App\Events\PaymentCaptured;
use App\Listeners\Notifications\OrderCreatedNotificationsListener;
use App\Listeners\SendInvoiceSendPayPalInvoiceEmailListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCaptured::class => [
            SendInvoiceSendPayPalInvoiceEmailListener::class
        ],
        CustomerOrderNotification::class => [
            OrderCreatedNotificationsListener::class
        ]
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
