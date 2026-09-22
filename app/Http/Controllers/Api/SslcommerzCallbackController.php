<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SslcommerzPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SslcommerzCallbackController extends Controller
{
    public function __construct(
        private readonly SslcommerzPaymentService $paymentService,
    ) {}

    /**
     * Handle SSLCommerz success callback.
     */
    public function success(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleSuccessfulCallback($request->all());

        return response()->json([
            'message' => 'Payment completed successfully.',
            'data' => [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'order_payment_status' => $payment->order?->payment_status,
            ],
        ]);
    }

    /**
     * Handle SSLCommerz failure callback.
     */
    public function failure(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleFailedCallback($request->all());

        return response()->json([
            'message' => 'Payment failed.',
            'data' => [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'order_payment_status' => $payment->order?->payment_status,
            ],
        ]);
    }

    /**
     * Handle SSLCommerz cancel callback.
     */
    public function cancel(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleCancelledCallback($request->all());

        return response()->json([
            'message' => 'Payment cancelled.',
            'data' => [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'order_payment_status' => $payment->order?->payment_status,
            ],
        ]);
    }

    /**
     * Handle SSLCommerz IPN callback.
     */
    public function ipn(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleSuccessfulCallback($request->all());

        return response()->json([
            'message' => 'IPN processed successfully.',
            'data' => [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'order_payment_status' => $payment->order?->payment_status,
            ],
        ]);
    }
}
