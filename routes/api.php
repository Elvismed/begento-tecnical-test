<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::post('auth/token', [AuthController::class, 'store'])
    ->middleware('throttle:login')
    ->name('auth.token');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('auth/logout', [AuthController::class, 'destroy'])->name('auth.logout');

    Route::get('products/{product}/prices', [ProductPriceController::class, 'index'])
        ->name('products.prices.index');

    Route::post('products/{product}/prices', [ProductPriceController::class, 'store'])
        ->name('products.prices.store');

    Route::apiResource('products', ProductController::class);
});
