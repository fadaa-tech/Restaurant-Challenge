<?php

namespace App\Providers;

use App\Services\OrderService;
use App\Services\OrderValidationService;
use App\Services\OrderCalculationService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\InvoiceService;
use App\Services\InventoryService;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderValidationService::class, function ($app) {
            return new OrderValidationService();
        });

        $this->app->singleton(OrderCalculationService::class, function ($app) {
            return new OrderCalculationService();
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            // In a real application, you would inject the actual payment gateway
            $checkoutPaymentGateway = new \stdClass(); // Placeholder
            return new PaymentService($checkoutPaymentGateway);
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService();
        });

        $this->app->singleton(InventoryService::class, function ($app) {
            // In a real application, you would inject the actual inventory manager
            $inventoryManager = new \stdClass(); // Placeholder
            return new InventoryService($inventoryManager);
        });

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderValidationService::class),
                $app->make(OrderCalculationService::class),
                $app->make(PaymentService::class),
                $app->make(NotificationService::class),
                $app->make(InvoiceService::class),
                $app->make(InventoryService::class)
            );
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
