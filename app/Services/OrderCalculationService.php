<?php

namespace App\Services;

use App\Models\Order;

class OrderCalculationService
{
    /**
     * Calculates the details of the order such as total amount, taxes, discounts, etc.
     *
     * @param Order $order
     * @return Order
     */
    public function calculateOrderDetails(Order $order): Order
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;
        
        foreach ($order->items as $item) {
            $subtotal += $item->quantity * $item->product->price;
        }
        
        // Calculate tax (assuming 10% tax rate)
        $tax = $subtotal * 0.10;
        
        // Calculate total
        $total = $subtotal + $tax - $discount;
        
        // Update order with calculated values
        $order->subtotal = $subtotal;
        $order->tax = $tax;
        $order->discount = $discount;
        $order->total = $total;
        
        return $order;
    }
}
