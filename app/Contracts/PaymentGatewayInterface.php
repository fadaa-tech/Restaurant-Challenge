<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function getAccessToken(): string;
    
    public function charge(Order $order, float $amount);

    public function capture(string $orderId): array;
}
