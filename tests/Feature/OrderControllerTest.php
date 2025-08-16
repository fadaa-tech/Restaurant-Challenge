<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $mockOrderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockOrderService = Mockery::mock(OrderService::class);
        $this->app->instance(OrderService::class, $this->mockOrderService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_order_successfully()
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        $orderData = [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'customer_phone' => '1234567890',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'special_instructions' => 'Extra cheese please'
                ]
            ]
        ];

        $this->mockOrderService->shouldReceive('placeOrder')
            ->once()
            ->andReturn(true);

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order',
                'order_id'
            ]);

        $this->assertDatabaseHas('orders', [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com'
        ]);
    }

    public function test_returns_error_when_rate_limit_exceeded()
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        // Mock rate limiter to return true (limit exceeded)
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(true);

        RateLimiter::shouldReceive('availableIn')
            ->once()
            ->andReturn(60);

        $orderData = [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'Rate limit exceeded for this branch. Please try again later.',
                'retry_after' => 60
            ]);
    }

    public function test_returns_error_when_order_service_fails()
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        // Mock rate limiter to return false (limit not exceeded)
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(false);

        $orderData = [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $this->mockOrderService->shouldReceive('placeOrder')
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Failed to process order. Please try again.'
            ]);
    }

    public function test_validates_required_fields()
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'branch_id',
                'name',
                'customer_email',
                'items'
            ]);
    }

    public function test_validates_branch_exists()
    {
        $product = Product::factory()->create();

        $orderData = [
            'branch_id' => 999, // Non-existent branch
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_validates_product_exists()
    {
        $branch = Branch::factory()->create();

        $orderData = [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_id' => 999, // Non-existent product
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_validates_quantity_range()
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->create();

        $orderData = [
            'branch_id' => $branch->id,
            'name' => 'Test Order',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0 // Invalid quantity
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }
}
