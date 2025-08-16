<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new OrderValidationService();
    }

    public function test_validate_order_with_items_returns_order()
    {
        $order = Order::factory()->create();
        $order->items = collect([OrderItem::factory()->make()]);

        $result = $this->validationService->validateOrder($order);

        $this->assertSame($order, $result);
    }

    public function test_validate_order_without_items_throws_exception()
    {
        $order = Order::factory()->create();
        $order->items = collect([]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Order must contain at least one item.');

        $this->validationService->validateOrder($order);
    }

    public function test_validate_order_with_null_items_throws_exception()
    {
        $order = Order::factory()->create();
        $order->items = null;

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Order must contain at least one item.');

        $this->validationService->validateOrder($order);
    }
}
