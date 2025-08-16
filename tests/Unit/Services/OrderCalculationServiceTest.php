<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderCalculationService $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculationService = new OrderCalculationService();
    }

    public function test_calculate_order_details_calculates_correctly()
    {
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 15.00]);

        $order = Order::factory()->create();
        $order->items = collect([
            OrderItem::factory()->make([
                'product_id' => $product1->id,
                'quantity' => 2,
                'product' => $product1
            ]),
            OrderItem::factory()->make([
                'product_id' => $product2->id,
                'quantity' => 1,
                'product' => $product2
            ])
        ]);

        $result = $this->calculationService->calculateOrderDetails($order);

        $this->assertEquals(35.00, $result->subtotal);
        $this->assertEquals(3.50, $result->tax); // 10% of 35.00
        $this->assertEquals(0.00, $result->discount);
        $this->assertEquals(38.50, $result->total);
    }

    public function test_calculate_order_details_with_zero_items()
    {
        $order = Order::factory()->create();
        $order->items = collect([]);

        $result = $this->calculationService->calculateOrderDetails($order);

        $this->assertEquals(0.00, $result->subtotal);
        $this->assertEquals(0.00, $result->tax);
        $this->assertEquals(0.00, $result->discount);
        $this->assertEquals(0.00, $result->total);
    }
}
