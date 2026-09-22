<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SslcommerzPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SslcommerzCallbackController extends Controller
{
    public function __construct(
        private readonly SslcommerzPaymentService $paymentService,
    ) {}

    /**
     * Handle SSLCommerz success callback and redirect to the storefront.
     */
    public function success(Request $request): RedirectResponse
    {
        try {
            $payment = $this->paymentService->handleSuccessfulCallback($request->all());

            return redirect()->route('shop.payments.success', [
                'order' => $payment->order?->order_number,
            ]);
        } catch (ValidationException) {
            return redirect()->route('shop.payments.failed', [
                'order' => $this->orderNumberFromTransactionId($request->input('tran_id')),
            ]);
        }
    }

    /**
     * Handle SSLCommerz failure callback and redirect to the storefront.
     */
    public function failure(Request $request): RedirectResponse
    {
        try {
            $payment = $this->paymentService->handleFailedCallback($request->all());

            return redirect()->route('shop.payments.failed', [
                'order' => $payment->order?->order_number,
            ]);
        } catch (ValidationException) {
            return redirect()->route('shop.payments.failed');
        }
    }

    /**
     * Handle SSLCommerz cancel callback and redirect to the storefront.
     */
    public function cancel(Request $request): RedirectResponse
    {
        try {
            $payment = $this->paymentService->handleCancelledCallback($request->all());

            return redirect()->route('shop.payments.cancelled', [
                'order' => $payment->order?->order_number,
            ]);
        } catch (ValidationException) {
            return redirect()->route('shop.payments.cancelled');
        }
    }

    private function orderNumberFromTransactionId(mixed $transactionId): ?string
    {
        if (! is_string($transactionId) || $transactionId === '') {
            return null;
        }

        return Payment::query()
            ->where('transaction_id', $transactionId)
            ->first()
            ?->order
            ?->order_number;
    }
}
