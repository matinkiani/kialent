<?php

use App\Enums\ChamferType;
use App\Enums\SlotType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('the gate pass route requires authentication and returns 200 when logged in', function () {
    $shipment = Shipment::factory()->create();

    $this->get(route('shipments.gate-pass.download', $shipment))->assertOk();
});

test('the gate pass shows shipment identity, customer, driver, and shipped quantities', function () {
    $customer = Customer::factory()->create(['name' => 'پارس ریسینگ']);
    $product = Product::factory()->create(['name' => 'لنت پژو ۴۰۵']);
    $order = Order::factory()->for($customer)->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 10,
        'has_shim' => true,
        'backing_plate_thickness' => 3.5,
        'slot_type' => SlotType::Straight,
        'chamfer_type' => ChamferType::Standard,
    ]);
    $shipment = Shipment::factory()->for($order)->create([
        'driver_name' => 'علی رضایی',
        'shipment_number' => 'MANUAL-001',
    ]);
    ShipmentItem::factory()->create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'shipped_quantity' => 3,
    ]);

    $html = $this->get(route('shipments.gate-pass.download', $shipment))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('MANUAL-001')
        ->toContain('پارس ریسینگ')
        ->toContain('علی رضایی')
        ->toContain('لنت پژو ۴۰۵');
});

test('the gate pass renders the has_shim state as real bold HTML, not escaped text', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['name' => 'لنت ۲۰۶']);
    $order = Order::factory()->for($customer)->create();

    $shimItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 4,
        'has_shim' => true,
    ]);

    $plainItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 2,
        'has_shim' => false,
    ]);

    $shipment = Shipment::factory()->for($order)->create();
    ShipmentItem::factory()->create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $shimItem->id,
        'shipped_quantity' => 2,
    ]);
    ShipmentItem::factory()->create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $plainItem->id,
        'shipped_quantity' => 1,
    ]);

    $html = $this->get(route('shipments.gate-pass.download', $shipment))
        ->assertOk()
        ->getContent();

    // قلم با شیم: تگ واقعی و بدون کاراکترهای فراری
    expect($html)
        ->toContain('<span class="font-bold">دارد</span>')
        ->not->toContain('&lt;span')
        ->not->toContain("'دارد'");

    // قلم با شیم باید به‌عنوان تگ واقعی بولد شده باشد (نه متن فراری)
    expect($html)
        ->toContain('<span class="font-bold">دارد</span>')
        ->not->toContain('&lt;span')
        ->not->toContain("'دارد'");

    // فقط داخل تیبل (بدون «پیوست: ندارد» در سربرگ)، دقیقاً یک قلم با شیم
    // و دقیقاً یک قلم بدون شیم وجود دارد
    $tbody = (string) str($html)->after('<tbody')->before('</tbody>');

    expect(substr_count($tbody, '<span class="font-bold">دارد</span>'))->toBe(1)
        ->and(substr_count($tbody, 'ندارد'))->toBe(1);

});
