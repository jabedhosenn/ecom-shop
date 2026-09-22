<?php

use App\Models\Order;
use App\Models\Payment;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Support\Facades\Http;

test('payment initiate endpoint creates payment and returns gateway url', function () {
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway',
            'sessionkey' => 'test-session-key',
        ]),
    ]);

    $order = Order::factory()->sslcommerz()->create();

    $response = $this->postJson('/api/payments/initiate', [
        'order_id' => $order->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order_id', $order->id)
        ->assertJsonPath('data.status', 'initiated')
        ->assertJsonPath('data.gateway_url', 'https://sandbox.sslcommerz.com/EasyCheckOut/test-gateway');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'initiated',
    ]);

    expect($order->fresh()->payment_method)->toBe('sslcommerz');
});

test('payment initiate endpoint validates order id', function () {
    $this->postJson('/api/payments/initiate', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order_id']);
});

test('payment initiate endpoint rejects already paid orders', function () {
    $order = Order::factory()->sslcommerz()->create([
        'payment_status' => 'paid',
    ]);

    $this->postJson('/api/payments/initiate', [
        'order_id' => $order->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['order_id']);
});

test('payment show endpoint returns payment details', function () {
    $payment = Payment::factory()->create();

    $this->getJson("/api/payments/{$payment->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $payment->id)
        ->assertJsonPath('data.transaction_id', $payment->transaction_id);
});

test('sslcommerz success callback validates and marks payment paid', function () {
    $payment = Payment::factory()->create([
        'status' => 'initiated',
        'amount' => 1250.00,
    ]);

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(true);
    Sslcommerz::shouldReceive('validatePayment')->once()->andReturn(true);

    $payload = [
        'tran_id' => $payment->transaction_id,
        'val_id' => 'VAL123456',
        'amount' => '1250.00',
        'card_type' => 'VISA-Dutch Bangla',
        'bank_tran_id' => 'BANK123456',
        'store_amount' => '1212.50',
        'verify_sign' => 'test-sign',
        'verify_key' => 'amount,bank_tran_id,base_fair,card_type,currency,status,store_amount,tran_id,val_id',
    ];

    $this->postJson('/api/payments/sslcommerz/success', $payload)
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'success');

    $payment->refresh();

    expect($payment->status)->toBe('success')
        ->and($payment->val_id)->toBe('VAL123456')
        ->and($payment->bank_tran_id)->toBe('BANK123456')
        ->and($payment->paid_at)->not->toBeNull()
        ->and($payment->order->payment_status)->toBe('paid');
});

test('sslcommerz failure callback marks payment failed', function () {
    $payment = Payment::factory()->create([
        'status' => 'initiated',
    ]);

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(true);

    $payload = [
        'tran_id' => $payment->transaction_id,
        'status' => 'FAILED',
        'verify_sign' => 'test-sign',
        'verify_key' => 'amount,status,tran_id',
    ];

    $this->postJson('/api/payments/sslcommerz/failure', $payload)
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'failed');

    expect($payment->fresh()->status)->toBe('failed')
        ->and($payment->order->fresh()->payment_status)->toBe('failed');
});

test('sslcommerz cancel callback marks payment cancelled', function () {
    $payment = Payment::factory()->create([
        'status' => 'initiated',
    ]);

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(true);

    $payload = [
        'tran_id' => $payment->transaction_id,
        'status' => 'CANCELLED',
        'verify_sign' => 'test-sign',
        'verify_key' => 'amount,status,tran_id',
    ];

    $this->postJson('/api/payments/sslcommerz/cancel', $payload)
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'cancelled');

    expect($payment->fresh()->status)->toBe('cancelled')
        ->and($payment->order->fresh()->payment_status)->toBe('cancelled');
});

test('sslcommerz callback rejects invalid hash', function () {
    $payment = Payment::factory()->create();

    Sslcommerz::shouldReceive('verifyHash')->once()->andReturn(false);

    $this->postJson('/api/payments/sslcommerz/success', [
        'tran_id' => $payment->transaction_id,
        'verify_sign' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['hash']);
});
