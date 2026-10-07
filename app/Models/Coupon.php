<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'discount_type',
    'discount_value',
    'minimum_order_amount',
    'maximum_discount_amount',
    'usage_limit',
    'used_count',
    'expires_at',
    'is_active',
])]
class Coupon extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'maximum_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected $attributes = [
        'minimum_order_amount' => 0,
        'used_count' => 0,
        'is_active' => true,
    ];
}
