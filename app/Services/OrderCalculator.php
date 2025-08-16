<?php
namespace App\Services;

use App\Models\Order;

class OrderCalculator
{
    public function calculate(Order $order): Order
    {
        $total = 0;

        foreach ($order->items as $item) {
            $total += $item->quantity * $item->price;
        }

        // Example tax & discount
        $tax = $total * 0.15;
        $discount = 0; // your discount logic

        $order->total_amount = $total + $tax - $discount;

        return $order;
    }
}
