<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => 'sslcommerz',
            'transaction_id' => 'PAY-SE-'.now()->format('Y').'-'.fake()->unique()->numerify('######'),
            'val_id' => null,
            'amount' => fake()->randomFloat(2, 500, 5000),
            'currency' => 'BDT',
            'status' => 'initiated',
            'card_type' => null,
            'bank_tran_id' => null,
            'store_amount' => null,
            'raw_response' => null,
            'paid_at' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (): array => [
            'status' => 'success',
            'val_id' => fake()->uuid(),
            'bank_tran_id' => fake()->uuid(),
            'card_type' => 'VISA-Dutch Bangla',
            'store_amount' => fake()->randomFloat(2, 500, 5000),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'cancelled',
        ]);
    }
}
