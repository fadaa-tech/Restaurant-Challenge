<?php

namespace App\Strategies\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PayPalPaymentGateway implements PaymentGatewayInterface
{
    private string $clientId;
    private string $secret;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
        $this->baseUrl = config('services.paypal.base_url');
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        return $response->json()['access_token'] ?? null;
    }

    public function charge(Order $order, float $amount)
    {
        $accessToken = $this->getAccessToken();

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    "reference_id" => $order->id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'return_url' => route('paypal.capture', ['payment_method' => 'paypal']),
                'cancel_url' => route('welcome'),
            ]
        ];

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders", $orderData);

        return $response->json();
    }

    public function capture(string $orderId): array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", ['invoice_id' => $orderId]);

        return $response->json();
    }
}
