<?php

use App\Enums\ChamferType;
use App\Enums\SlotType;
use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\ListShipments;
use App\Filament\Resources\Shipments\Pages\ViewShipment;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(asSuperAdmin());
});

/**
 * یک سفارش با یک قلم، و (اختیاری) ارسالِ قبلیِ بخشی از سفارش می‌سازد
 * و «باقی‌مانده» را برمی‌گرداند.
 */
function shipmentOrderWithRemaining(int $quantity, int $alreadyShipped = 0): array
{
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create();
    $product = Product::factory()->create(['weight' => 2.5]);
    $item = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => $quantity,
        'has_shim' => false,
        'slot_type' => SlotType::Straight,
        'chamfer_type' => ChamferType::Standard,
    ]);

    if ($alreadyShipped > 0) {
        $shipment = Shipment::factory()->for($order)->create();
        ShipmentItem::factory()->create([
            'shipment_id' => $shipment->id,
            'order_item_id' => $item->id,
            'shipped_quantity' => $alreadyShipped,
        ]);
    }

    return [
        'order' => $order,
        'item' => $item,
        'product' => $product,
        'alreadyShipped' => $alreadyShipped,
        'remaining' => $quantity - $alreadyShipped,
    ];
}

test('the shipments list page can be rendered', function () {
    Livewire::test(ListShipments::class)->assertSuccessful();
});

test('the shipments list shows the customer name and hides the order number column by default', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10);

    Shipment::factory()->for($setup['order'])->create();

    $component = Livewire::test(ListShipments::class);

    $component->assertSuccessful()
        ->assertTableColumnVisible('order.customer.name')
        ->assertTableColumnExists('order.order_number');

    expect($component->instance()->isTableColumnToggledHidden('order.order_number'))->toBeTrue();
});

test('a shipment can be created for an order with a remaining quantity', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10);

    Livewire::test(CreateShipment::class)
        ->fillForm([
            'order_id' => $setup['order']->id,
            'driver_name' => 'علی رضایی',
            'vehicle_plate' => 'ب ۱۲ ۳۴۵',
            'total_master_boxes' => 2,
            'shipped_at' => now()->toDateString(),
            'shipmentItems' => [
                [
                    'order_item_id' => $setup['item']->id,
                    'remaining_quantity' => $setup['remaining'],
                    'shipped_quantity' => $setup['remaining'],
                    'box_quantity' => 2,
                    'product_weight' => $setup['product']->weight,
                ],
            ],
            'total_weight' => 25,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $shipment = Shipment::sole();

    expect($shipment->order_id)->toBe($setup['order']->id)
        ->and($shipment->shipment_number)->toBe('14-001-S01')
        ->and($shipment->shipmentItems)->toHaveCount(1)
        ->and($shipment->shipmentItems->first()->shipped_quantity)->toBe(10)
        ->and($shipment->shipmentItems->first()->box_quantity)->toBe(2);

    expect($shipment->total_weight)->toBe('0.025');
});

test('a shipped quantity of zero is allowed', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10);

    Livewire::test(CreateShipment::class)
        ->fillForm([
            'order_id' => $setup['order']->id,
            'driver_name' => 'علی',
            'total_master_boxes' => 1,
            'shipped_at' => now()->toDateString(),
            'shipmentItems' => [
                [
                    'order_item_id' => $setup['item']->id,
                    'shipped_quantity' => 0,
                    'box_quantity' => 0,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('master boxes of zero are rejected', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10);

    Livewire::test(CreateShipment::class)
        ->fillForm([
            'order_id' => $setup['order']->id,
            'driver_name' => 'علی',
            'total_master_boxes' => 0,
            'shipped_at' => now()->toDateString(),
            'shipmentItems' => [
                [
                    'order_item_id' => $setup['item']->id,
                    'remaining_quantity' => 10,
                    'shipped_quantity' => 5,
                    'box_quantity' => 2,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['total_master_boxes']);
});

test('shipping more than the remaining quantity across shipments is rejected', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10, alreadyShipped: 4);

    Livewire::test(CreateShipment::class)
        ->fillForm([
            'order_id' => $setup['order']->id,
            'driver_name' => 'علی',
            'total_master_boxes' => 1,
            'shipped_at' => now()->toDateString(),
            'shipmentItems' => [
                [
                    'order_item_id' => $setup['item']->id,
                    'shipped_quantity' => 7, // باقی‌مانده فقط ۶ است
                    'box_quantity' => 3,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['shipmentItems.0.shipped_quantity']);
});

test('previous shipments are counted towards the remaining quantity', function () {
    $setup = shipmentOrderWithRemaining(quantity: 10, alreadyShipped: 4);

    // ۴ دست قبلاً در حواله‌ای دیگر ارسال شده؛ پس باقی‌مانده برای حواله بعدی ۶ است
    expect($setup['remaining'])->toBe(6);

    Livewire::test(CreateShipment::class)
        ->fillForm([
            'order_id' => $setup['order']->id,
            'driver_name' => 'علی',
            'total_master_boxes' => 1,
            'shipped_at' => now()->toDateString(),
            'shipmentItems' => [
                [
                    'order_item_id' => $setup['item']->id,
                    'remaining_quantity' => 6,
                    'shipped_quantity' => 6, // دقیقاً به اندازه باقی‌مانده
                    'box_quantity' => 2,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('a shipment view shows shipment details and item weights', function () {
    $setup = shipmentOrderWithRemaining(quantity: 500);

    $shipment = Shipment::factory()->for($setup['order'])->create([
        'driver_name' => 'علی رضایی',
        'vehicle_plate' => 'ب ۱۲ ۳۴۵',
        'total_master_boxes' => 2,
    ]);
    ShipmentItem::factory()->create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $setup['item']->id,
        'shipped_quantity' => 300,
    ]);

    Livewire::test(ViewShipment::class, ['record' => $shipment->id])
        ->assertSuccessful()
        ->assertSee('14-001-S01')
        ->assertSee('14-001')
        ->assertSee('علی رضایی')
        ->assertSee('ب ۱۲ ۳۴۵')
        ->assertSee('500')  // تعداد سفارش
        ->assertSee('300')  // مقدار ارسالی
        ->assertSee('200')  // تعداد باقیمانده
        ->assertSee('0.750');
});
