<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function store($orderData)
    {
        $order = Order::create($orderData);

        $order->items()->createMany($orderData['items']);

        return $order;
    }

    public function updateByKey($condition, array $data)
    {
        return Order::where($condition)->update($data);
    }

    public function findById(int $id)
    {
        return Order::find($id);
    }
}
