<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SslcommerzCallbackController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')->name('api.payments.')->group(function (): void {
    Route::post('initiate', [PaymentController::class, 'initiate'])->name('initiate');
    Route::get('refund-status', [PaymentController::class, 'refundStatus'])->name('refund-status');

    Route::prefix('sslcommerz')->name('sslcommerz.')->group(function (): void {
        Route::post('success', [SslcommerzCallbackController::class, 'success'])->name('success');
        Route::post('failure', [SslcommerzCallbackController::class, 'failure'])->name('failure');
        Route::post('cancel', [SslcommerzCallbackController::class, 'cancel'])->name('cancel');
        Route::post('ipn', [SslcommerzCallbackController::class, 'ipn'])->name('ipn');
    });

    Route::get('{payment}', [PaymentController::class, 'show'])->name('show');
    Route::post('{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
});
