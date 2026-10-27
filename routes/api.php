<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::get('products/{product}/prices', [ProductPriceController::class, 'index'])
    ->name('products.prices.index');

Route::post('products/{product}/prices', [ProductPriceController::class, 'store'])
    ->name('products.prices.store');

Route::apiResource('products', ProductController::class);
