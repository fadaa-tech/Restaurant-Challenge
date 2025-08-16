<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * The order service instance.
     *
     * @var OrderService
     */
    private OrderService $orderService;

    /**
     * The OrderController constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $branchId = $validatedData['branch_id'] ?? null;
        
        // Check branch-based rate limiting
        if (!$this->checkBranchRateLimit($branchId)) {
            return response()->json([
                'error' => 'Rate limit exceeded for this branch. Please try again later.',
                'retry_after' => $this->getRetryAfter($branchId)
            ], 429);
        }

        try {
            // Create the order
            $order = Order::create($validatedData);
            
            // Process the order using the service
            $success = $this->orderService->placeOrder($order);
            
            if (!$success) {
                return response()->json([
                    'error' => 'Failed to process order. Please try again.'
                ], 500);
            }

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items', 'branch'),
                'order_id' => $order->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $validatedData
            ]);

            return response()->json([
                'error' => 'Failed to create order. Please try again.'
            ], 500);
        }
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

    /**
     * Check if the branch has exceeded its rate limit.
     *
     * @param int|null $branchId
     * @return bool
     */
    private function checkBranchRateLimit(?int $branchId): bool
    {
        $key = $this->getRateLimitKey($branchId);
        $maxAttempts = $this->getMaxAttempts($branchId);
        $decayMinutes = 1; // 1 minute window

        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Get the rate limit key for the branch.
     *
     * @param int|null $branchId
     * @return string
     */
    private function getRateLimitKey(?int $branchId): string
    {
        $branchId = $branchId ?? 'no_branch';
        return "order_creation:branch_{$branchId}";
    }

    /**
     * Get the maximum attempts allowed for the branch.
     *
     * @param int|null $branchId
     * @return int
     */
    private function getMaxAttempts(?int $branchId): int
    {
        // Different rate limits for different branches
        // Premium branches get higher limits
        if ($branchId === 1) { // Assuming branch ID 1 is premium
            return 30; // 30 orders per minute
        }
        
        if ($branchId === 2) { // Assuming branch ID 2 is standard
            return 20; // 20 orders per minute
        }
        
        // Default rate limit
        return 15; // 15 orders per minute
    }

    /**
     * Get the retry after time for the branch.
     *
     * @param int|null $branchId
     * @return int
     */
    private function getRetryAfter(?int $branchId): int
    {
        $key = $this->getRateLimitKey($branchId);
        return RateLimiter::availableIn($key);
    }

    /**
     * Hit the rate limiter for the branch.
     *
     * @param int|null $branchId
     * @return void
     */
    private function hitRateLimit(?int $branchId): void
    {
        $key = $this->getRateLimitKey($branchId);
        $decayMinutes = 1;
        
        RateLimiter::hit($key, $decayMinutes * 60);
    }
}
