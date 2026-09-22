<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access admin wishlists', function () {
    $this->get(route('admin.wishlists.index'))
        ->assertRedirect(route('login'));
});

test('customers cannot access admin wishlists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.wishlists.index'))
        ->assertForbidden();
});

test('admins can view the wishlists index', function () {
    $admin = User::factory()->admin()->create();
    $wishlist = Wishlist::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.wishlists.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/wishlists/Index')
            ->has('wishlists', 1)
            ->has('wishlists.0', fn (Assert $wishlistPage) => $wishlistPage
                ->where('id', $wishlist->id)
                ->has('user', fn (Assert $userPage) => $userPage
                    ->where('id', $wishlist->user_id)
                    ->has('name')
                    ->has('email')
                )
                ->has('product', fn (Assert $productPage) => $productPage
                    ->where('id', $wishlist->product_id)
                    ->has('name')
                    ->has('slug')
                    ->has('price')
                    ->has('stock_status')
                    ->has('is_active')
                    ->has('image')
                )
                ->has('created_at')
            )
            ->has('filters')
            ->where('filters.search', '')
        );
});

test('admins can search wishlists by customer name', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['name' => 'Wishlist Customer']);
    $wishlist = Wishlist::factory()->for($customer)->create();
    Wishlist::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.wishlists.index', ['search' => 'Wishlist Customer']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/wishlists/Index')
            ->has('wishlists', 1)
            ->where('wishlists.0.id', $wishlist->id)
            ->where('filters.search', 'Wishlist Customer')
        );
});

test('admins can search wishlists by product name', function () {
    $admin = User::factory()->admin()->create();
    $wishlist = Wishlist::factory()->for(
        Product::factory()->create(['name' => 'Searchable Wishlist Product']),
        'product',
    )->create();
    Wishlist::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.wishlists.index', ['search' => 'Searchable Wishlist']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/wishlists/Index')
            ->has('wishlists', 1)
            ->where('wishlists.0.id', $wishlist->id)
            ->where('filters.search', 'Searchable Wishlist')
        );
});
