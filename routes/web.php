<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::prefix('/payment')->controller(App\Http\Controllers\PaymentController::class)->group(function () {
    Route::get('/payment_response', fn() => view('paypal.response'))->name('paypal.response');
});
