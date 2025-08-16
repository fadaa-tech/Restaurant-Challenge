<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Services\OrderCalculationService;
use App\Services\OrderService;
use App\Services\OrderValidationService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\InvoiceService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private $mockValidationService;
    private $mockCalculationService;
    private $mockPaymentService;
    private $mockNotificationService;
    private $mockInvoiceService;
    private $mockInventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockValidationService = Mockery::mock(OrderValidationService::class);
        $this->mockCalculationService = Mockery::mock(OrderCalculationService::class);
        $this->mockPaymentService = Mockery::mock(PaymentService::class);
        $this->mockNotificationService = Mockery::mock(NotificationService::class);
        $this->mockInvoiceService = Mockery::mock(InvoiceService::class);
        $this->mockInventoryService = Mockery::mock(InventoryService::class);

        $this->orderService = new OrderService(
            $this->mockValidationService,
            $this->mockCalculationService,
            $this->mockPaymentService,
            $this->mockNotificationService,
            $this->mockInvoiceService,
            $this->mockInventoryService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_place_order_successfully()
    {
        $order = Order::factory()->create();

        $this->mockValidationService->shouldReceive('validateOrder')
            ->once()
            ->with($order)
            ->andReturn($order);

        $this->mockCalculationService->shouldReceive('calculateOrderDetails')
            ->once()
            ->with($order)
            ->andReturn($order);

        $this->mockPaymentService->shouldReceive('processPayment')
            ->once()
            ->with($order)
            ->andReturn(true);

        $this->mockNotificationService->shouldReceive('notifyCustomer')
            ->once()
            ->with($order)
            ->andReturn(true);

        $this->mockInvoiceService->shouldReceive('sendInvoice')
            ->once()
            ->with($order)
            ->andReturn(true);

        $this->mockInventoryService->shouldReceive('updateInventory')
            ->once()
            ->with($order)
            ->andReturn(true);

        $result = $this->orderService->placeOrder($order);

        $this->assertTrue($result);
    }

    public function test_place_order_fails_when_payment_fails()
    {
        $order = Order::factory()->create();

        $this->mockValidationService->shouldReceive('validateOrder')
            ->once()
            ->with($order)
            ->andReturn($order);

        $this->mockCalculationService->shouldReceive('calculateOrderDetails')
            ->once()
            ->with($order)
            ->andReturn($order);

        $this->mockPaymentService->shouldReceive('processPayment')
            ->once()
            ->with($order)
            ->andReturn(false);

        $result = $this->orderService->placeOrder($order);

        $this->assertFalse($result);
    }

    public function test_place_order_fails_when_validation_fails()
    {
        $order = Order::factory()->create();

        $this->mockValidationService->shouldReceive('validateOrder')
            ->once()
            ->with($order)
            ->andThrow(new \Exception('Validation failed'));

        $result = $this->orderService->placeOrder($order);

        $this->assertFalse($result);
    }
}
