<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * The payment gateway for processing payments.
     *
     * @var mixed
     */
    private $checkoutPaymentGateway;

    /**
     * The PaymentService constructor.
     *
     * @param mixed $checkoutPaymentGateway
     */
    public function __construct($checkoutPaymentGateway)
    {
        $this->checkoutPaymentGateway = $checkoutPaymentGateway;
    }

    /**
     * Uses the checkout payment gateway to process the payment.
     *
     * @param Order $order
     * @return bool
     */
    public function processPayment(Order $order): bool
    {
        try {
            $this->checkoutPaymentGateway->processPayment($order->total);
            return true;
        } catch (\Exception $e) {
            // Log payment failure
            Log::error('Payment processing failed for order: ' . $order->id, [
                'error' => $e->getMessage(),
                'amount' => $order->total
            ]);
            return false;
        }
    }
}
