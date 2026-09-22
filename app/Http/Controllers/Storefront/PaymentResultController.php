<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentResultController extends Controller
{
    /**
     * Display the payment success page.
     */
    public function success(Request $request): Response
    {
        return Inertia::render('shop/PaymentSuccess', [
            'order' => $this->orderSummary($request->query('order')),
        ]);
    }

    /**
     * Display the payment failed page.
     */
    public function failed(Request $request): Response
    {
        return Inertia::render('shop/PaymentFailed', [
            'order' => $this->orderSummary($request->query('order')),
        ]);
    }

    /**
     * Display the payment cancelled page.
     */
    public function cancelled(Request $request): Response
    {
        return Inertia::render('shop/PaymentCancelled', [
            'order' => $this->orderSummary($request->query('order')),
        ]);
    }

    /**
     * @return array{orderNumber: string, total: float, paymentLabel: string}|null
     */
    private function orderSummary(?string $orderNumber): ?array
    {
        if (! filled($orderNumber)) {
            return null;
        }

        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->first();

        if ($order === null) {
            return null;
        }

        return [
            'orderNumber' => $order->order_number,
            'total' => (float) $order->total,
            'paymentLabel' => 'SSLCommerz',
        ];
    }
}
