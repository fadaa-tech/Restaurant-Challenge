<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/products', App\Http\Controllers\ProductController::class);
Route::apiResource('/orders', App\Http\Controllers\OrderController::class);
Route::apiResource('/order_items', App\Http\Controllers\OrderItemController::class);


Route::prefix('/payment')->controller(App\Http\Controllers\PaymentController::class)->group(function () {
    Route::post('/create_order', 'processPayment')->name('paypal.charge');
    Route::get('/capture_order', 'capturePayment')->name('paypal.capture');
});