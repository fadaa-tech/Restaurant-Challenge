<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class RevenueManager
{
    /**
     * Calculate total revenue for all orders.
     *
     * @return float
     */
   public static function calculateTotalRevenue(): float
{
    return Cache::remember('daily_revenue_' . today()->toDateString(), 3600, function () {
        return (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', today())
            ->selectRaw('SUM(order_items.quantity * order_items.price) as total')
            ->value('total');
    });
}

}
