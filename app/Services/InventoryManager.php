<?php
namespace App\Services;

use App\Models\Order;

class InventoryManager
{
    public function updateInventory(Order $order): void
    {
        // Your logic to reduce inventory by order items
        foreach ($order->items as $item) {
            $product = $item->product;
            $product->decrement('stock', $item->quantity);
        }
    }
}
