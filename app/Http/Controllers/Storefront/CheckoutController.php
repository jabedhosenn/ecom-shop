<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SslcommerzPaymentService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly SslcommerzPaymentService $paymentService,
    ) {}

    /**
     * Display the checkout page.
     */
    public function index(): Response|RedirectResponse
    {
        if ($this->cartService->totalQty() === 0) {
            return redirect()
                ->route('shop.cart')
                ->with('error', 'Your cart is empty. Add items before checkout.');
        }

        $delivery = config('shop.delivery');

        return Inertia::render('shop/Checkout', [
            'districts' => config('shop.districts'),
            'deliveryCharges' => [
                'insideDhaka' => (float) $delivery['inside_dhaka'],
                'outsideDhaka' => (float) $delivery['outside_dhaka'],
                'dhakaDistrict' => $delivery['dhaka_district'],
            ],
        ]);
    }

    /**
     * Place an order and complete checkout.
     */
    public function store(StoreCheckoutRequest $request): RedirectResponse|SymfonyResponse
    {
        $validated = $request->validated();

        if ($validated['payment_method'] === 'sslcommerz') {
            $order = $this->orderService->placeSslcommerzOrder($validated);
            $result = $this->paymentService->initiate($order);

            if ($result['gateway_url'] === null) {
                return redirect()
                    ->route('shop.payments.failed', ['order' => $order->order_number])
                    ->with('error', 'Unable to start online payment. Please try again.');
            }

            return Inertia::location($result['gateway_url']);
        }

        $order = $this->orderService->placeCodOrder($validated);

        return redirect()
            ->route('shop.orders.success')
            ->with('order', [
                'orderNumber' => $order->order_number,
                'total' => (float) $order->total,
                'paymentLabel' => 'Cash on Delivery',
            ]);
    }
}
