<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\WishlistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::patch('coupons/{coupon}/status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
        Route::resource('coupons', CouponController::class)->except(['show']);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
        Route::resource('wishlists', WishlistController::class)->only(['index']);
    });
