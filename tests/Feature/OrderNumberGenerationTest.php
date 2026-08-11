<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('orders receive sequential, customer-scoped order numbers', function () {
    $customer = Customer::factory()->create(['code' => '14']);

    $first = Order::factory()->for($customer)->create();
    $second = Order::factory()->for($customer)->create();
    $third = Order::factory()->for($customer)->create();

    expect($first->order_number)->toBe('14-001')
        ->and($second->order_number)->toBe('14-002')
        ->and($third->order_number)->toBe('14-003');
});

test('order number sequences are independent per customer', function () {
    $customerA = Customer::factory()->create(['code' => 'AL']);
    $customerB = Customer::factory()->create(['code' => 'BK']);

    $orderA = Order::factory()->for($customerA)->create();
    $orderB = Order::factory()->for($customerB)->create();

    expect($orderA->order_number)->toBe('AL-001')
        ->and($orderB->order_number)->toBe('BK-001');
});

test('an explicitly assigned order number is not overwritten', function () {
    $customer = Customer::factory()->create(['code' => 'ZZ']);

    $order = Order::factory()->for($customer)->make();
    $order->order_number = 'ZZ-999';
    $order->save();

    expect($order->fresh()->order_number)->toBe('ZZ-999')
        ->and($customer->fresh()->next_order_sequence)->toBe(1);
});
