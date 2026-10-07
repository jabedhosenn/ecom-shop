<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;

trait CouponValidationRules
{
    /**
     * @return array<string, mixed>
     */
    protected function couponRules(?int $couponId = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'discount_type' => ['required', Rule::in(['percentage', 'flat'])],
            'discount_value' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                Rule::when($this->input('discount_type') === 'percentage', ['lte:100']),
            ],
            'minimum_order_amount' => ['required', 'numeric', 'decimal:0,2', 'gte:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
