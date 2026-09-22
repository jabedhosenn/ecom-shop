<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use HasinHayder\Sslcommerz\Data\RefundResponse;
use HasinHayder\Sslcommerz\Data\RefundStatus;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SslcommerzPaymentService
{
    /**
     * Initiate an SSLCommerz payment for the given order.
     *
     * @return array{payment: Payment, gateway_url: string|null}
     */
    public function initiate(Order $order): array
    {
        if ($order->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'order_id' => 'This order has already been paid.',
            ]);
        }

        $existingPayment = $order->payments()
            ->where('status', 'initiated')
            ->latest()
            ->first();

        if ($existingPayment !== null) {
            $gatewayUrl = $existingPayment->raw_response['GatewayPageURL'] ?? null;

            if ($gatewayUrl !== null) {
                return [
                    'payment' => $existingPayment,
                    'gateway_url' => $gatewayUrl,
                ];
            }
        }

        $transactionId = $this->generateTransactionId($order);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'amount' => $order->total,
            'currency' => config('sslcommerz.store.currency', 'BDT'),
            'status' => 'initiated',
        ]);

        $order->loadMissing('items');

        $quantity = (int) $order->items->sum('quantity');
        $quantity = max($quantity, 1);
        $productName = $order->items->first()?->product_name ?? "Order {$order->order_number}";

        $response = Sslcommerz::setOrder(
            (float) $order->total,
            $transactionId,
            $productName,
            'E-commerce',
        )
            ->setCustomer(
                $order->customer_name,
                $order->email,
                $order->phone,
                $order->address,
                $order->area,
                $order->district,
            )
            ->setShippingInfo(
                $quantity,
                $order->address,
                $order->customer_name,
                $order->area,
                $order->district,
            )
            ->makePayment();

        $payment->update([
            'raw_response' => $response->toArray(),
        ]);

        if (! $response->success()) {
            $payment->update(['status' => 'failed']);

            throw ValidationException::withMessages([
                'payment' => $response->failedReason() ?? 'Unable to initiate SSLCommerz payment.',
            ]);
        }

        if ($order->payment_method !== 'sslcommerz') {
            $order->update(['payment_method' => 'sslcommerz']);
        }

        return [
            'payment' => $payment->fresh(),
            'gateway_url' => $response->gatewayPageURL(),
        ];
    }

    /**
     * Handle a successful SSLCommerz callback or IPN payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleSuccessfulCallback(array $payload): Payment
    {
        return $this->processValidatedCallback($payload, 'success');
    }

    /**
     * Handle a failed SSLCommerz callback payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleFailedCallback(array $payload): Payment
    {
        if (! Sslcommerz::verifyHash($payload)) {
            throw ValidationException::withMessages([
                'hash' => 'Invalid SSLCommerz signature.',
            ]);
        }

        $payment = $this->findPaymentByTransactionId((string) ($payload['tran_id'] ?? ''));

        $payment->update([
            'status' => 'failed',
            'val_id' => $payload['val_id'] ?? $payment->val_id,
            'raw_response' => array_merge($payment->raw_response ?? [], $payload),
        ]);

        $payment->order?->update(['payment_status' => 'failed']);

        return $payment->fresh(['order']);
    }

    /**
     * Handle a cancelled SSLCommerz callback payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleCancelledCallback(array $payload): Payment
    {
        if (! Sslcommerz::verifyHash($payload)) {
            throw ValidationException::withMessages([
                'hash' => 'Invalid SSLCommerz signature.',
            ]);
        }

        $payment = $this->findPaymentByTransactionId((string) ($payload['tran_id'] ?? ''));

        $payment->update([
            'status' => 'cancelled',
            'raw_response' => array_merge($payment->raw_response ?? [], $payload),
        ]);

        $payment->order?->update(['payment_status' => 'cancelled']);

        return $payment->fresh(['order']);
    }

    /**
     * Refund a completed SSLCommerz payment.
     */
    public function refund(Payment $payment, float $amount, string $reason): RefundResponse
    {
        if ($payment->status !== 'success') {
            throw ValidationException::withMessages([
                'payment' => 'Only successful payments can be refunded.',
            ]);
        }

        if ($payment->bank_tran_id === null) {
            throw ValidationException::withMessages([
                'payment' => 'This payment does not have a bank transaction id.',
            ]);
        }

        return Sslcommerz::refundPayment($payment->bank_tran_id, $amount, $reason);
    }

    /**
     * Check the refund status for a refund reference id.
     */
    public function refundStatus(string $refundRefId): RefundStatus
    {
        return Sslcommerz::checkRefundStatus($refundRefId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function processValidatedCallback(array $payload, string $status): Payment
    {
        if (! Sslcommerz::verifyHash($payload)) {
            throw ValidationException::withMessages([
                'hash' => 'Invalid SSLCommerz signature.',
            ]);
        }

        $payment = $this->findPaymentByTransactionId((string) ($payload['tran_id'] ?? ''));

        $isValid = Sslcommerz::validatePayment(
            $payload,
            (string) $payment->transaction_id,
            (float) $payment->amount,
            (string) $payment->currency,
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'payment' => 'SSLCommerz payment validation failed.',
            ]);
        }

        return DB::transaction(function () use ($payment, $payload, $status): Payment {
            $payment->update([
                'status' => $status,
                'val_id' => $payload['val_id'] ?? $payment->val_id,
                'card_type' => $payload['card_type'] ?? $payment->card_type,
                'bank_tran_id' => $payload['bank_tran_id'] ?? $payment->bank_tran_id,
                'store_amount' => $payload['store_amount'] ?? $payment->store_amount,
                'raw_response' => array_merge($payment->raw_response ?? [], $payload),
                'paid_at' => now(),
            ]);

            $payment->order?->update(['payment_status' => 'paid']);

            return $payment->fresh(['order']);
        });
    }

    private function findPaymentByTransactionId(string $transactionId): Payment
    {
        if ($transactionId === '') {
            throw ValidationException::withMessages([
                'tran_id' => 'Transaction id is required.',
            ]);
        }

        $payment = Payment::query()
            ->where('transaction_id', $transactionId)
            ->first();

        if ($payment === null) {
            throw ValidationException::withMessages([
                'tran_id' => 'Payment not found for the given transaction id.',
            ]);
        }

        return $payment;
    }

    private function generateTransactionId(Order $order): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $transactionId = sprintf(
                'PAY-%s-%s',
                $order->order_number,
                strtoupper(substr(uniqid(), -8)),
            );

            if (! Payment::query()->where('transaction_id', $transactionId)->exists()) {
                return $transactionId;
            }
        }

        throw new RuntimeException('Unable to generate a unique payment transaction id.');
    }
}
