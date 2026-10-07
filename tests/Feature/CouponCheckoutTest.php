<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * @return array<string, mixed>
 */
function couponCheckoutPayload(array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'Rina Akter',
        'phone' => '01712345678',
        'email' => 'rina@example.com',
        'district' => 'Dhaka',
        'area' => 'Dhanmondi',
        'address' => 'House 12, Road 5, Dhanmondi',
        'payment_method' => 'cod',
    ], $overrides);
}

function placeCouponCheckout(TestCase $testCase, Coupon $coupon, array $checkoutOverrides = []): Order
{
    $testCase->seed(DatabaseSeeder::class);

    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();

    $testCase->post(route('shop.cart.store'), [
        'product_id' => $product->id,
        'qty' => 1,
    ]);

    $testCase->post(route('shop.checkout.store'), couponCheckoutPayload([
        'coupon_code' => $coupon->code,
        ...$checkoutOverrides,
    ]))->assertRedirect(route('shop.orders.success'));

    return Order::query()->firstOrFail();
}

test('checkout applies a percentage coupon using the server calculated subtotal', function () {
    $coupon = Coupon::query()->create([
        'code' => 'SAVE10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $order = placeCouponCheckout($this, $coupon, [
        'discount_amount' => 999999,
    ]);

    expect((float) $order->discount_amount)->toBe(round((float) $order->subtotal * 0.1, 2))
        ->and((float) $order->total)->toBe(round((float) $order->subtotal - (float) $order->discount_amount + (float) $order->delivery_charge, 2))
        ->and($order->coupon_id)->toBe($coupon->id)
        ->and($order->coupon_code)->toBe($coupon->code)
        ->and($coupon->fresh()->used_count)->toBe(1);
});

test('checkout uses the coupon applied to the cart without trusting checkout totals', function () {
    $this->seed(DatabaseSeeder::class);

    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    $coupon = Coupon::query()->create([
        'code' => 'CART10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->post(route('shop.cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => $coupon->code])
        ->assertRedirect();

    $this->get(route('shop.checkout'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('shop/Checkout')
            ->where('coupon.code', $coupon->code)
            ->where('discountAmount', round((float) $product->price * 2 * 0.1, 2))
        );

    $this->post(route('shop.checkout.store'), couponCheckoutPayload([
        'discount_amount' => 0,
        'subtotal' => 0,
        'total' => 0,
    ]))->assertRedirect(route('shop.orders.success'));

    $order = Order::query()->firstOrFail();
    $expectedSubtotal = round((float) $product->price * 2, 2);

    expect((float) $order->subtotal)->toBe($expectedSubtotal)
        ->and((float) $order->discount_amount)->toBe(round($expectedSubtotal * 0.1, 2))
        ->and((float) $order->total)->toBe(round($expectedSubtotal - $order->discount_amount + $order->delivery_charge, 2))
        ->and($order->coupon_id)->toBe($coupon->id)
        ->and($order->coupon_code)->toBe($coupon->code)
        ->and($coupon->fresh()->used_count)->toBe(1)
        ->and(session()->has('cart_coupon_code'))->toBeFalse();
});

test('coupon usage and cart are preserved when order creation fails', function () {
    $this->seed(DatabaseSeeder::class);

    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    $coupon = Coupon::query()->create([
        'code' => 'FAIL10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->post(route('shop.cart.store'), ['product_id' => $product->id]);
    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => $coupon->code]);

    OrderStatusHistory::creating(function (): void {
        throw new RuntimeException('Simulated order creation failure.');
    });

    $this->withoutExceptionHandling();

    expect(fn () => $this->post(route('shop.checkout.store'), couponCheckoutPayload()))
        ->toThrow(RuntimeException::class, 'Simulated order creation failure.');

    expect(Order::query()->count())->toBe(0)
        ->and($coupon->fresh()->used_count)->toBe(0)
        ->and(session()->get('cart_coupon_code'))->toBe($coupon->code);

    $this->get(route('shop.cart'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('cart.qty', 1));
});

test('checkout applies a fixed coupon for its configured amount', function () {
    $coupon = Coupon::query()->create([
        'code' => 'FLAT15',
        'discount_type' => 'fixed',
        'discount_value' => 15,
    ]);

    $order = placeCouponCheckout($this, $coupon);

    expect((float) $order->discount_amount)->toBe(15.0)
        ->and((float) $order->total)->toBe(round((float) $order->subtotal - 15 + (float) $order->delivery_charge, 2));
});

test('checkout caps a fixed coupon at the subtotal and never creates a negative total', function () {
    $coupon = Coupon::query()->create([
        'code' => 'FLAT999',
        'discount_type' => 'fixed',
        'discount_value' => 999999,
    ]);

    $order = placeCouponCheckout($this, $coupon);

    expect((float) $order->discount_amount)->toBe((float) $order->subtotal)
        ->and((float) $order->total)->toBe((float) $order->delivery_charge)
        ->and((float) $order->total)->toBeGreaterThanOrEqual(0);
});

test('checkout rejects coupons that are inactive, expired, exhausted, or below minimum', function (array $attributes, string $message): void {
    test()->seed(DatabaseSeeder::class);

    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();

    test()->post(route('shop.cart.store'), ['product_id' => $product->id]);

    $coupon = Coupon::query()->create([
        'code' => 'INVALID',
        'discount_type' => 'fixed',
        'discount_value' => 10,
        ...$attributes,
    ]);

    test()->post(route('shop.checkout.store'), couponCheckoutPayload([
        'coupon_code' => $coupon->code,
    ]))
        ->assertSessionHasErrors('coupon_code')
        ->assertSessionHasErrors(['coupon_code' => $message]);

    expect(Order::query()->count())->toBe(0)
        ->and($coupon->fresh()->used_count)->toBe($coupon->used_count);
})->with([
    'inactive' => [['is_active' => false], 'This coupon is inactive.'],
    'expired' => [['expires_at' => now()->subMinute()], 'This coupon has expired.'],
    'usage limit reached' => [['usage_limit' => 1, 'used_count' => 1], 'This coupon has reached its usage limit.'],
    'minimum not met' => [['minimum_order_amount' => 999999], 'Your order does not meet this coupon minimum amount.'],
    'percentage above 100' => [
        ['discount_type' => 'percentage', 'discount_value' => 101],
        'This coupon has an invalid percentage discount.',
    ],
]);
