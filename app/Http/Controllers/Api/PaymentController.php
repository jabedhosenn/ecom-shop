<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitiatePaymentRequest;
use App\Http\Requests\Api\RefundPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\SslcommerzPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly SslcommerzPaymentService $paymentService,
    ) {}

    /**
     * Initiate an SSLCommerz payment for an order.
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $order = Order::query()->findOrFail($request->integer('order_id'));

        $result = $this->paymentService->initiate($order);

        return response()->json([
            'message' => 'Payment initiated successfully.',
            'data' => $this->paymentResource($result['payment'], $result['gateway_url']),
        ], 201);
    }

    /**
     * Show a payment record.
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load('order:id,order_number,payment_status,total');

        return response()->json([
            'data' => $this->paymentResource($payment),
        ]);
    }

    /**
     * Refund a successful payment.
     */
    public function refund(RefundPaymentRequest $request, Payment $payment): JsonResponse
    {
        $response = $this->paymentService->refund(
            $payment,
            (float) $request->input('amount'),
            (string) $request->input('reason'),
        );

        return response()->json([
            'message' => 'Refund request submitted.',
            'data' => $response->toArray(),
        ]);
    }

    /**
     * Check refund status by refund reference id.
     */
    public function refundStatus(Request $request): JsonResponse
    {
        $request->validate([
            'refund_ref_id' => ['required', 'string', 'max:100'],
        ]);

        $status = $this->paymentService->refundStatus((string) $request->input('refund_ref_id'));

        return response()->json([
            'data' => $status->toArray(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentResource(Payment $payment, ?string $gatewayUrl = null): array
    {
        return [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'order' => $payment->relationLoaded('order') ? [
                'id' => $payment->order?->id,
                'order_number' => $payment->order?->order_number,
                'payment_status' => $payment->order?->payment_status,
                'total' => $payment->order !== null ? (float) $payment->order->total : null,
            ] : null,
            'gateway' => $payment->gateway,
            'transaction_id' => $payment->transaction_id,
            'val_id' => $payment->val_id,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'card_type' => $payment->card_type,
            'bank_tran_id' => $payment->bank_tran_id,
            'store_amount' => $payment->store_amount !== null ? (float) $payment->store_amount : null,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'gateway_url' => $gatewayUrl,
            'created_at' => $payment->created_at?->toIso8601String(),
            'updated_at' => $payment->updated_at?->toIso8601String(),
        ];
    }
}
