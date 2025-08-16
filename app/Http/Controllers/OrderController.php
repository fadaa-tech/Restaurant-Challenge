<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Traits\ResponseTrait;
use App\Services\OrderService;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    use ResponseTrait;
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

      /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {

        return $this->success($this->orderService->placeOrder($request->validated()), "Order created successfully") ? : $this->error("Could not create order");
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
