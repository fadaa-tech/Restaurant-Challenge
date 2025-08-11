<?php

namespace App\Http\Controllers;

use App\Events\PaymentCaptured;
use App\Http\Requests\ProcessPaymentRequest;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService,  protected OrderService $orderService) {}

    public function processPayment(ProcessPaymentRequest $request)
    {
        $order_id = $request->input('order_id');

        $result = $this->paymentService->processPayment($order_id);
        
        return response()->json($result);
    }

    public function capturePayment(Request $request)
    {
        $orderId = $request->query('token');
        
        $data = $this->paymentService->capturePayment($orderId);

        return redirect()->route('paypal.response', $data);
    }
}
