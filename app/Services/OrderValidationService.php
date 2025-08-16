<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderValidationService
{
    /**
     * Validates the order data.
     *
     * @param Order $order
     * @return Order
     * @throws ValidationException
     */
    public function validateOrder(Order $order): Order
    {
        // Validate the order data.
        // This would include validation logic for order items, customer info, etc.
        
        if (!$order->items || $order->items->isEmpty()) {
            throw new ValidationException('Order must contain at least one item.');
        }
        
        return $order;
    }
}
