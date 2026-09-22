<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access the dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('customers cannot access the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('admins can view the analytics dashboard', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->delivered()->create([
        'total' => 1500,
        'placed_at' => now(),
    ]);
    Order::factory()->create([
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);
    Product::factory()->create(['sold_count' => 12]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard/Index')
            ->has('overview', fn (Assert $overview) => $overview
                ->where('total_revenue', 1500)
                ->where('total_orders', 2)
                ->where('pending_orders', 1)
                ->has('average_order_value')
                ->has('total_customers')
                ->has('new_customers_this_month')
                ->has('total_products')
                ->has('active_products')
                ->has('out_of_stock_products')
                ->has('total_wishlists')
                ->etc()
            )
            ->has('revenue_chart', 30)
            ->has('orders_by_status', 5)
            ->has('payment_status_breakdown', 4)
            ->has('payment_method_breakdown', 2)
            ->has('top_products')
            ->has('top_categories')
            ->has('recent_orders', 2)
            ->has('recent_orders.0', fn (Assert $order) => $order
                ->has('order_number')
                ->has('customer_name')
                ->has('total')
                ->has('status')
                ->has('payment_status')
                ->etc()
            )
        );
});

test('dashboard revenue chart includes paid order revenue for today', function () {
    $admin = User::factory()->admin()->create();

    Order::factory()->delivered()->create([
        'total' => 2500,
        'placed_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview.total_revenue', 2500)
        );

    $chart = collect($response->original->getData()['page']['props']['revenue_chart']);
    $todayPoint = $chart->firstWhere('date', now()->toDateString());

    expect($todayPoint)->not->toBeNull()
        ->and($todayPoint['revenue'])->toBe(2500.0);
});
