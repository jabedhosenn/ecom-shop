<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function validCouponPayload(array $overrides = []): array
{
    return array_merge([
        'code' => 'SAVE10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'minimum_order_amount' => 0,
        'maximum_discount_amount' => null,
        'usage_limit' => null,
        'expires_at' => null,
        'status' => 'active',
    ], $overrides);
}

test('guests and customers cannot access coupon administration', function () {
    $this->get(route('admin.coupons.index'))
        ->assertRedirect(route('login'));

    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('admin.coupons.index'))
        ->assertForbidden();
});

test('admins can view the coupon list', function () {
    $admin = User::factory()->admin()->create();
    Coupon::query()->create([
        'code' => 'SAVE10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.coupons.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/coupons/Index')
            ->has('coupons', 1)
            ->where('coupons.0.code', 'SAVE10')
            ->where('coupons.0.discount_type', 'percentage')
            ->where('coupons.0.status', 'active')
        );
});

test('admins can create coupons and flat values use the existing fixed database type', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.coupons.store'), validCouponPayload([
            'code' => ' save-flat ',
            'discount_type' => 'flat',
            'discount_value' => 15,
            'minimum_order_amount' => 50,
            'usage_limit' => 20,
            'status' => 'inactive',
        ]))
        ->assertRedirect(route('admin.coupons.index'));

    $this->assertDatabaseHas('coupons', [
        'code' => 'SAVE-FLAT',
        'discount_type' => 'fixed',
        'discount_value' => 15,
        'minimum_order_amount' => 50,
        'usage_limit' => 20,
        'is_active' => false,
    ]);
});

test('admins can update a coupon while retaining its used count', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::query()->create([
        'code' => 'ORIGINAL',
        'discount_type' => 'fixed',
        'discount_value' => 12,
        'used_count' => 4,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.coupons.update', $coupon), validCouponPayload([
            'code' => 'updated',
            'discount_type' => 'flat',
            'discount_value' => 25,
            'status' => 'inactive',
        ]))
        ->assertRedirect(route('admin.coupons.index'));

    expect($coupon->fresh()->code)->toBe('UPDATED')
        ->and($coupon->fresh()->discount_type)->toBe('fixed')
        ->and((float) $coupon->fresh()->discount_value)->toBe(25.0)
        ->and($coupon->fresh()->used_count)->toBe(4)
        ->and($coupon->fresh()->is_active)->toBeFalse();
});

test('coupon codes must be unique and percentage values cannot exceed 100', function () {
    $admin = User::factory()->admin()->create();
    Coupon::query()->create([
        'code' => 'TAKEN',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.coupons.create'))
        ->post(route('admin.coupons.store'), validCouponPayload([
            'code' => 'taken',
        ]))
        ->assertSessionHasErrors('code');

    $this->from(route('admin.coupons.create'))
        ->post(route('admin.coupons.store'), validCouponPayload([
            'code' => 'TOO-MUCH',
            'discount_value' => 100.01,
        ]))
        ->assertSessionHasErrors('discount_value');
});

test('admins can activate and deactivate coupons', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::query()->create([
        'code' => 'TOGGLE',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.coupons.toggle-status', $coupon))
        ->assertRedirect(route('admin.coupons.index'));

    expect($coupon->fresh()->is_active)->toBeFalse();

    $this->patch(route('admin.coupons.toggle-status', $coupon))
        ->assertRedirect(route('admin.coupons.index'));

    expect($coupon->fresh()->is_active)->toBeTrue();
});

test('admins can delete coupons and past orders retain coupon snapshots', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::query()->create([
        'code' => 'ORDER-COUPON',
        'discount_type' => 'fixed',
        'discount_value' => 10,
    ]);
    $order = Order::factory()->create([
        'coupon_id' => $coupon->id,
        'coupon_code' => $coupon->code,
        'discount_amount' => 10,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.coupons.destroy', $coupon))
        ->assertRedirect(route('admin.coupons.index'));

    $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);

    expect($order->fresh()->coupon_id)->toBeNull()
        ->and($order->fresh()->coupon_code)->toBe('ORDER-COUPON')
        ->and((float) $order->fresh()->discount_amount)->toBe(10.0);
});
