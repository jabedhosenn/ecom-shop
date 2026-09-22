<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

/**
 * @return array<string, mixed>
 */
function sslcommerzCheckoutPayload(array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'Rina Akter',
        'phone' => '01712345678',
        'email' => 'rina@example.com',
        'district' => 'Dhaka',
        'area' => 'Dhanmondi',
        'address' => 'House 12, Road 5, Dhanmondi',
        'notes' => 'Call before delivery',
        'payment_method' => 'sslcommerz',
    ], $overrides);
}

test('sslcommerz checkout places order and redirects to gateway', function () {
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway',
            'sessionkey' => 'test-session-key',
        ]),
    ]);

    $this->seed(DatabaseSeeder::class);

    $product = Product::where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->first();

    $this->post(route('shop.cart.store'), ['product_id' => $product->id, 'qty' => 1]);

    $response = $this->post(route('shop.checkout.store'), sslcommerzCheckoutPayload());

    $response->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway');

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('sslcommerz')
        ->and($order->payment_status)->toBe('pending');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'initiated',
    ]);
});

test('sslcommerz checkout uses inertia location for external gateway redirect', function () {
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway',
            'sessionkey' => 'test-session-key',
        ]),
    ]);

    $this->seed(DatabaseSeeder::class);

    $product = Product::where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->first();

    $this->post(route('shop.cart.store'), ['product_id' => $product->id, 'qty' => 1]);

    $this->post(route('shop.checkout.store'), sslcommerzCheckoutPayload(), [
        'X-Inertia' => 'true',
    ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway');
});

test('payment success page renders with order details', function () {
    $order = Order::factory()->sslcommerz()->create([
        'payment_status' => 'paid',
    ]);

    $this->get(route('shop.payments.success', ['order' => $order->order_number]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/PaymentSuccess')
            ->where('order.orderNumber', $order->order_number)
            ->where('order.paymentLabel', 'SSLCommerz')
        );
});

test('payment failed page renders with inertia', function () {
    $this->get(route('shop.payments.failed'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/PaymentFailed')
            ->where('order', null)
        );
});

test('payment cancelled page renders with inertia', function () {
    $this->get(route('shop.payments.cancelled'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/PaymentCancelled')
            ->where('order', null)
        );
});

test('storefront sslcommerz success callback redirects to payment success page', function () {
    $payment = Payment::factory()->create([
        'status' => 'initiated',
        'amount' => 1250.00,
    ]);

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(true);
    Sslcommerz::shouldReceive('validatePayment')->once()->andReturn(true);

    $payload = [
        'tran_id' => $payment->transaction_id,
        'val_id' => 'VAL123456',
        'amount' => '1250.00',
        'card_type' => 'VISA-Dutch Bangla',
        'bank_tran_id' => 'BANK123456',
        'store_amount' => '1212.50',
        'verify_sign' => 'test-sign',
        'verify_key' => 'amount,bank_tran_id,card_type,currency,status,store_amount,tran_id,val_id',
    ];

    $this->post(route('shop.payments.sslcommerz.success'), $payload)
        ->assertRedirect(route('shop.payments.success', [
            'order' => $payment->order->order_number,
        ]));

    expect($payment->fresh()->status)->toBe('success')
        ->and($payment->order->fresh()->payment_status)->toBe('paid');
});

test('storefront sslcommerz cancel callback redirects to cancelled page', function () {
    $payment = Payment::factory()->create([
        'status' => 'initiated',
    ]);

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(true);

    $payload = [
        'tran_id' => $payment->transaction_id,
        'status' => 'CANCELLED',
        'verify_sign' => 'test-sign',
        'verify_key' => 'amount,status,tran_id',
    ];

    $this->post(route('shop.payments.sslcommerz.cancel'), $payload)
        ->assertRedirect(route('shop.payments.cancelled', [
            'order' => $payment->order->order_number,
        ]));

    expect($payment->fresh()->status)->toBe('cancelled');
});
