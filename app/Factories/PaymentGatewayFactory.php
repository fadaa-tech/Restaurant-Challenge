<?php

namespace App\Factories;

use App\Contracts\PaymentGatewayInterface;
use App\Strategies\Payments\PayPalPaymentGateway;

class PaymentGatewayFactory
{
    public static function pay(string $method): PaymentGatewayInterface {
        return match ($method) {
            'paypal' => new PayPalPaymentGateway(),
            default => throw new \Exception("Payment method {$method} is not supported."),
        };
    }
}