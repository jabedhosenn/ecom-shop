<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Validate and calculate a coupon without consuming one use.
     *
     * @return array{coupon: Coupon, discount_amount: float}
     */
    public function calculate(string $code, float $subtotal): array
    {
        return $this->resolveAndCalculate($code, $subtotal, false);
    }

    /**
     * Validate and reserve a coupon use while creating an order.
     *
     * Call this inside the order transaction so the coupon row lock and use
     * count update are rolled back if order creation fails.
     *
     * @return array{coupon: Coupon, discount_amount: float}
     */
    public function apply(string $code, float $subtotal): array
    {
        $result = $this->resolveAndCalculate($code, $subtotal, true);
        $result['coupon']->increment('used_count');

        return $result;
    }

    /**
     * @return array{coupon: Coupon, discount_amount: float}
     */
    private function resolveAndCalculate(string $code, float $subtotal, bool $lockForUpdate): array
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            throw ValidationException::withMessages([
                'coupon_code' => 'Enter a valid coupon code.',
            ]);
        }

        if (! is_finite($subtotal) || $subtotal < 0) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The order subtotal is invalid.',
            ]);
        }

        $query = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$code]);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if ($coupon === null) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon code is invalid.',
            ]);
        }

        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon is inactive.',
            ]);
        }

        if ($coupon->expires_at !== null && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has expired.',
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has reached its usage limit.',
            ]);
        }

        if ($subtotal < (float) $coupon->minimum_order_amount) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Your order does not meet this coupon minimum amount.',
            ]);
        }

        $discountValue = (float) $coupon->discount_value;

        if ($discountValue < 0) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has an invalid discount value.',
            ]);
        }

        if ($coupon->maximum_discount_amount !== null && (float) $coupon->maximum_discount_amount < 0) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has an invalid maximum discount.',
            ]);
        }

        $discountAmount = match ($coupon->discount_type) {
            'percentage' => $this->percentageDiscount($subtotal, $discountValue),
            'fixed' => min($subtotal, $discountValue),
            default => throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has an invalid discount type.',
            ]),
        };

        if ($coupon->maximum_discount_amount !== null) {
            $discountAmount = min($discountAmount, (float) $coupon->maximum_discount_amount);
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => round(min($subtotal, max(0, $discountAmount)), 2),
        ];
    }

    private function percentageDiscount(float $subtotal, float $percentage): float
    {
        if ($percentage > 100) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has an invalid percentage discount.',
            ]);
        }

        return $subtotal * ($percentage / 100);
    }
}
