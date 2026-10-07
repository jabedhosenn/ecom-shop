<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;

class CartService
{
    private const CART_SESSION_KEY = 'cart_id';

    private const COUPON_SESSION_KEY = 'cart_coupon_code';

    /**
     * In-request cache of the resolved Cart row.
     * null  = not looked up yet
     * false = looked up and does not exist
     */
    private Cart|false|null $resolvedCart = null;

    public function __construct(
        private readonly ProductImageService $productImageService,
    ) {}

    // ─── Cart resolution ──────────────────────────────────────────────────────

    /**
     * Find the cart for the current visitor without creating one.
     *
     * - Authenticated users → matched by user_id
     * - Guests             → cart primary key is stored in the session
     */
    private function findCart(): ?Cart
    {
        if ($this->resolvedCart !== null) {
            return $this->resolvedCart === false ? null : $this->resolvedCart;
        }

        if (auth()->id()) {
            $cart = Cart::where('user_id', auth()->id())->first();
        } else {
            $cartId = session(self::CART_SESSION_KEY);
            $cart = $cartId ? Cart::find((int) $cartId) : null;
        }

        $this->resolvedCart = $cart ?? false;

        return $cart;
    }

    /**
     * Find or create the cart for the current visitor.
     * For guests, the new cart's primary key is persisted in the session.
     */
    private function resolveCart(): Cart
    {
        $existing = $this->findCart();

        if ($existing) {
            return $existing;
        }

        if (auth()->id()) {
            $cart = Cart::create(['user_id' => auth()->id()]);
        } else {
            $cart = Cart::create(['session_id' => session()->getId()]);
            session([self::CART_SESSION_KEY => $cart->id]);
        }

        $this->resolvedCart = $cart;

        return $cart;
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Total item count — cheap: one SUM query on cart_items.
     */
    public function totalQty(): int
    {
        $cart = $this->findCart();

        if (! $cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    /**
     * Full cart items resolved with product details.
     *
     * @return list<array{productId: int, name: string, slug: string, price: float, img: string, qty: int, inStock: bool}>
     */
    public function items(): array
    {
        $cart = $this->findCart();

        if (! $cart) {
            return [];
        }

        return $cart->items()
            ->with([
                'product',
                'product.images' => fn ($q) => $q->where('is_primary', true),
            ])
            ->get()
            ->filter(fn (CartItem $item) => $item->product?->is_active)
            ->map(fn (CartItem $item): array => [
                'productId' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'price' => (float) $item->product->price,
                'img' => $this->productImageService->resolveUrl($item->product->images->first()?->image_path) ?? '',
                'qty' => $item->quantity,
                'inStock' => $item->product->stock_status === 'in_stock',
            ])
            ->values()
            ->all();
    }

    /**
     * Calculate the subtotal from the current cart items.
     */
    public function subtotal(): float
    {
        return round(collect($this->items())->sum(
            fn (array $item): float => $item['price'] * $item['qty'],
        ), 2);
    }

    public function couponCode(): ?string
    {
        $couponCode = session(self::COUPON_SESSION_KEY);

        return filled($couponCode) ? (string) $couponCode : null;
    }

    public function setCouponCode(string $couponCode): void
    {
        session([self::COUPON_SESSION_KEY => $couponCode]);
    }

    public function clearCoupon(): void
    {
        session()->forget(self::COUPON_SESSION_KEY);
    }

    /**
     * Add a product or increment its quantity.
     */
    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->resolveCart();
        $qty = max(1, $qty);

        $existing = $cart->items()->where('product_id', $productId)->first();

        if ($existing) {
            $existing->increment('quantity', $qty);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $qty,
            ]);
        }
    }

    /**
     * Set the exact quantity of a cart item (minimum 1).
     */
    public function update(int $productId, int $qty): void
    {
        $cart = $this->findCart();

        if (! $cart) {
            return;
        }

        $cart->items()
            ->where('product_id', $productId)
            ->update(['quantity' => max(1, $qty)]);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(int $productId): void
    {
        $cart = $this->findCart();

        if (! $cart) {
            return;
        }

        $cart->items()->where('product_id', $productId)->delete();

        if ($this->totalQty() === 0) {
            $this->clearCoupon();
        }
    }

    /**
     * Remove all items from the cart (keeps the cart row itself).
     */
    public function clear(): void
    {
        $this->clearCoupon();

        $this->clearItems();
    }

    /**
     * Remove all cart items without changing the applied coupon session state.
     */
    public function clearItems(): void
    {
        $cart = $this->findCart();

        if (! $cart) {
            return;
        }

        $cart->items()->delete();
    }
}
