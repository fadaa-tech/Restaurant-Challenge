<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Branch\BranchController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// auth for user
// login
Route::post('/login', [App\Http\Controllers\Api\Auth\AuthController::class, 'loginUser']);

Route::apiResource('/products', App\Http\Controllers\ProductController::class);


Route::apiResource('/orders', App\Http\Controllers\OrderController::class)->except(['store']);
Route::middleware(['throttle:branch_orders'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::apiResource('/order_items', App\Http\Controllers\OrderItemController::class);
Route::apiResource('branches', BranchController::class);
