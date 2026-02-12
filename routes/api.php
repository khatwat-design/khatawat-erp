<?php

use App\Http\Controllers\StoreApiController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\StorefrontController;
use App\Http\Controllers\Api\SaaSController;
use Illuminate\Support\Facades\Route;

Route::prefix('store')->group(function (): void {
    Route::get('/', [StoreApiController::class, 'index']);
    Route::get('/products', [StoreApiController::class, 'products']);
    Route::get('/products/{id}', [StoreApiController::class, 'showProduct']);
    Route::get('/banners', [StoreApiController::class, 'banners']);
    Route::post('/orders', [StoreApiController::class, 'createOrder']);
});

Route::post('/orders', [OrderController::class, 'store']);

Route::prefix('storefront')->group(function (): void {
    Route::get('/settings', [StorefrontController::class, 'getSettings']);
    Route::get('/products', [StorefrontController::class, 'getProducts']);
    Route::post('/validate-coupon', [StorefrontController::class, 'validateCoupon']);
    Route::post('/orders', [StorefrontController::class, 'createOrder']);
});

Route::post('/saas/register', [SaaSController::class, 'registerMerchant']);
