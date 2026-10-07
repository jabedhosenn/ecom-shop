<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
    ) {}

    /**
     * Display the shopping cart page.
     */
    public function index(): Response
    {
        $subtotal = $this->cartService->subtotal();
        $coupon = null;
        $couponError = null;
        $discountAmount = 0.0;
        $couponCode = $this->cartService->couponCode();

        if ($couponCode !== null && $this->cartService->totalQty() > 0) {
            try {
                $result = $this->couponService->calculate($couponCode, $subtotal);
                $coupon = ['code' => $result['coupon']->code];
                $discountAmount = $result['discount_amount'];
            } catch (ValidationException $exception) {
                $this->cartService->clearCoupon();
                $couponError = $exception->errors()['coupon_code'][0];
            }
        } elseif ($couponCode !== null) {
            $this->cartService->clearCoupon();
        }

        return Inertia::render('shop/Cart', [
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discountAmount' => $discountAmount,
            'total' => max(0, round($subtotal - $discountAmount, 2)),
            'couponError' => $couponError,
        ]);
    }

    /**
     * Apply a coupon to the current cart.
     */
    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        if ($this->cartService->items() === []) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Add items to your cart before applying a coupon.',
            ]);
        }

        $result = $this->couponService->calculate(
            $data['coupon_code'],
            $this->cartService->subtotal(),
        );

        $this->cartService->setCouponCode($result['coupon']->code);

        return back();
    }

    /**
     * Remove the applied coupon from the current cart.
     */
    public function removeCoupon(): RedirectResponse
    {
        $this->cartService->clearCoupon();

        return back();
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::where('id', $data['product_id'])
            ->where('is_active', true)
            ->where('stock_status', 'in_stock')
            ->first();

        if (! $product) {
            return back()->with('error', 'This product is not available.');
        }

        $this->cartService->add($data['product_id'], $data['qty'] ?? 1);

        return back();
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, int $productId): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cartService->update($productId, $data['qty']);

        return back();
    }

    /**
     * Remove a product from the cart.
     */
    public function destroy(int $productId): RedirectResponse
    {
        $this->cartService->remove($productId);

        return back();
    }

    /**
     * Remove all items from the cart.
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return back();
    }
}
