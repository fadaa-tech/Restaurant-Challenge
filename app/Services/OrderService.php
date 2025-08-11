<?php

namespace App\Services;

use App\Events\CustomerOrderNotification;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(protected OrderRepository $orderRepo, protected ProductRepository $productRepo, protected PaymentService $paymentService) {}

    public function placeOrder($order)
    {
        $this->calculateOrderDetails($order);

        try {
            DB::beginTransaction();

            $order = $this->orderRepo->store($order);

            $this->productRepo->updateInventory($order->items);            

            DB::commit();

            event(new CustomerOrderNotification($order));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
        }

        return $order;
    }

    private function calculateOrderDetails(&$order)
    {
        $items =  collect($order['items']);

        $products = $this->productRepo->getProductsByIds($items->pluck('product_id'))->keyBy('id');

        $order['total_amount'] = 0;

        $items->transform(function ($item) use ($products, &$order) {
            $price = $products->get($item['product_id'])->price;
            $order['total_amount'] += $item['quantity'] * $price;
            $item['price'] = $price;
            return $item;
        });

        $order['items'] = $items->values()->toArray();
    }
}
