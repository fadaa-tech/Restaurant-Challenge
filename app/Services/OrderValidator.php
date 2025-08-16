<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderValidator
{
    public function validate(Order $order): void
    {
        // Example simple validation
        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Order must have at least one item.']);
        }
        // Add more validation rules here
    }
}
