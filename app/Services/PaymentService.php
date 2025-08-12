<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Events\PaymentCaptured;
use App\Repositories\OrderRepository;

class PaymentService
{
    public function __construct(private PaymentGatewayInterface $paymentGateway, protected OrderRepository $orderRepo)
    {
    }

    public function processPayment($order_id)
    {
        $order = $this->orderRepo->findById($order_id);

        $amount = $order->total_amount;

        $result = $this->paymentGateway->charge($order, $amount);

        if ($result['status'] === 'CREATED') {
            $this->orderRepo->updateByKey(['id' => $order_id], ['status' => 'processing', 'payment_id' => $result['id']]);

            $approvalLink = array_filter($result['links'], fn($link) => $link['rel'] === 'approve');
    
            return [
                'status' => $result['status'],
                'data' => [
                    'approval_link' => reset($approvalLink)['href']
                ]
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Payment gateway error.'
        ];
    }

    public function capturePayment($token)
    {
        $captureResponse = $this->paymentGateway->capture($token);

        $data['status'] = 'failed';
        
        if (isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {

            event(new PaymentCaptured($captureResponse));

            $this->orderRepo->updateByKey(['payment_id' => $captureResponse['id']], ['status' => 'completed']);

            $paymentDetails = $captureResponse['purchase_units'][0]['payments']['captures'][0];

            $data = [
                'status' => 'success',
                'token' => $token,
                'amount' => $paymentDetails['amount']['value'],
                'currency' => $paymentDetails['amount']['currency_code']
            ];
        }

        return $data;
    }
}
