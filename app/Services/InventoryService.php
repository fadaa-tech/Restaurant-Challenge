<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * The inventory manager for updating inventory.
     *
     * @var mixed
     */
    private $inventoryManager;

    /**
     * The InventoryService constructor.
     *
     * @param mixed $inventoryManager
     */
    public function __construct($inventoryManager)
    {
        $this->inventoryManager = $inventoryManager;
    }

    /**
     * Updates the inventory based on the order.
     *
     * @param Order $order
     * @return bool
     */
    public function updateInventory(Order $order): bool
    {
        try {
            foreach ($order->items as $item) {
                $this->inventoryManager->updateInventory(
                    $item->product_id,
                    $item->quantity
                );
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Inventory update failed for order: ' . $order->id, [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
