<?php
namespace App\Services;

use Exception;

class PaymentProcessor
{
    protected $gateway;

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }

    public function process(float $amount): void
    {
        // Here you call your payment gateway
        $success = $this->gateway->processPayment($amount);

        if (! $success) {
            throw new Exception('Payment failed');
        }
    }
}
