<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('shipment numbers are sequential per order, based on the order number', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create();

    $first = Shipment::factory()->for($order)->create();
    $second = Shipment::factory()->for($order)->create();

    expect($first->shipment_number)->toBe('14-001-S01')
        ->and($second->shipment_number)->toBe('14-001-S02');
});

test('shipment number sequences are independent per order', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $orderA = Order::factory()->for($customer)->create();
    $orderB = Order::factory()->for($customer)->create();

    $shipmentA = Shipment::factory()->for($orderA)->create();
    $shipmentB = Shipment::factory()->for($orderB)->create();

    expect($shipmentA->shipment_number)->toBe('14-001-S01')
        ->and($shipmentB->shipment_number)->toBe('14-002-S01');
});

test('shipment numbers remain sequential after deleting an earlier shipment', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create();

    $first = Shipment::factory()->for($order)->create();
    $second = Shipment::factory()->for($order)->create();

    $first->delete();
    $third = Shipment::factory()->for($order)->create();

    expect($third->shipment_number)->toBe('14-001-S03');
});

test('an explicitly assigned shipment number is not overwritten', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create();

    $shipment = Shipment::factory()->for($order)->make();
    $shipment->shipment_number = 'MANUAL-XXX';
    $shipment->save();

    expect($shipment->fresh()->shipment_number)->toBe('MANUAL-XXX');
});

test('shipment numbers round out sequence counting, not shipment id gaps', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create();

    $shipment = Shipment::factory()->for($order)->create();

    // ساخت حواله جدید بدون فیلد شماره: توالی باید از روی تعداد حواله‌های قبلی حساب شود
    $second = new Shipment([
        'order_id' => $order->id,
        'shipped_at' => now(),
    ]);
    $second->save();

    expect($second->fresh()->shipment_number)->toBe('14-001-S02');
});
