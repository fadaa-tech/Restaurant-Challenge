<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

class RevenueManager
{
    /**
     * Calculate total revenue for all orders.
     *
     * @return float
     */
    public static function calculateTotalRevenue(): float
    {
        $totalRevenue = 0.0;

        // Order::query()->chunk(100, function ($orders) use (&$totalRevenue) {
        //     $orders->each(function ($order) use (&$totalRevenue) {
        //         $order->items()->each(function ($orderItem) use (&$totalRevenue) {
        //             $totalRevenue += $orderItem->quantity * $orderItem->price;
        //         });
        //     });
        // });



        self::firstWay($totalRevenue);

        // self::secondWay($totalRevenue);

        // self::thirdWay($totalRevenue);

        return $totalRevenue;
    }

    private static function firstWay(&$totalRevenue) {
        // First way for calculating total revenue for all orders
        $totalRevenue = OrderItem::selectRaw('SUM(quantity * price) as totalRevenue')->value('totalRevenue');
    }

    private static function secondWay(&$totalRevenue) {
        // Second way for calculating total revenue for specific order with some conditions
        $orders = Order::where('status', 'completed')->get()->pluck('id');
        $totalRevenue = OrderItem::whereIn('order_id', $orders)->selectRaw('SUM(quantity * price) as totalRevenue')->value('totalRevenue');
    }

    private static function thirdWay(&$totalRevenue) {
        // Third way if we can use totalAmount that already exists in the orders table
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
    }
}
