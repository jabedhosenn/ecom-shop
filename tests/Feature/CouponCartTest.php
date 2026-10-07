<?php

use App\Models\Coupon;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed(DatabaseSeeder::class);
});

test('cart shows its items without coupon pricing props when no coupon is applied', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();

    $this->post(route('shop.cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ]);

    $this->get(route('shop.cart'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/Cart')
            ->where('cart.qty', 2)
            ->has('cart.items', 1)
        );
});

test('a coupon selected from checkout is calculated by the server', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    $coupon = Coupon::query()->create([
        'code' => 'SAVE10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->post(route('shop.cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ]);

    $this->post(route('shop.cart.coupon.apply'), [
        'coupon_code' => ' save10 ',
        'discount_amount' => 999999,
    ])->assertRedirect();

    $subtotal = round((float) $product->price * 2, 2);
    $discountAmount = round($subtotal * 0.1, 2);

    $this->assertSame('SAVE10', session('cart_coupon_code'));
    $this->get(route('shop.checkout'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/Checkout')
            ->where('subtotal', fn ($value): bool => (float) $value === $subtotal)
            ->where('coupon.code', $coupon->code)
            ->where('discountAmount', fn ($value): bool => (float) $value === $discountAmount)
        );
});

test('cart rejects invalid coupon codes without applying them', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();

    $this->post(route('shop.cart.store'), ['product_id' => $product->id]);

    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => 'NOTREAL'])
        ->assertSessionHasErrors('coupon_code');

    expect(session()->has('cart_coupon_code'))->toBeFalse();
});

test('removing a cart coupon restores the original total', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    Coupon::query()->create([
        'code' => 'FLAT15',
        'discount_type' => 'fixed',
        'discount_value' => 15,
    ]);

    $this->post(route('shop.cart.store'), ['product_id' => $product->id]);
    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => 'FLAT15']);

    $this->delete(route('shop.cart.coupon.remove'))->assertRedirect();

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    expect(session()->has('cart_coupon_code'))->toBeFalse();

    $this->get(route('shop.checkout'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('coupon', null)
            ->where('discountAmount', 0)
        );
});

test('cart recalculates an applied coupon when item quantities change', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    Coupon::query()->create([
        'code' => 'SAVE10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->post(route('shop.cart.store'), ['product_id' => $product->id]);
    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => 'SAVE10']);
    $this->patch(route('shop.cart.update', $product->id), ['qty' => 3]);

    $subtotal = round((float) $product->price * 3, 2);

    $this->get(route('shop.checkout'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('coupon.code', 'SAVE10')
            ->where('subtotal', fn ($value): bool => (float) $value === $subtotal)
            ->where('discountAmount', fn ($value): bool => (float) $value === round($subtotal * 0.1, 2))
        );
});

test('cart removes an applied coupon when the updated subtotal is no longer eligible', function () {
    $product = Product::query()
        ->where('is_active', true)
        ->where('stock_status', 'in_stock')
        ->firstOrFail();
    Coupon::query()->create([
        'code' => 'MINIMUM',
        'discount_type' => 'fixed',
        'discount_value' => 15,
        'minimum_order_amount' => round((float) $product->price * 2, 2),
    ]);

    $this->post(route('shop.cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
    $this->post(route('shop.cart.coupon.apply'), ['coupon_code' => 'MINIMUM']);
    $this->patch(route('shop.cart.update', $product->id), ['qty' => 1]);

    $this->get(route('shop.checkout'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('coupon', null)
            ->where('discountAmount', 0)
            ->where('couponError', 'Your order does not meet this coupon minimum amount.')
        );

    expect(session()->has('cart_coupon_code'))->toBeFalse();
});
