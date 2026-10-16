<?php

use App\Enums\ChamferType;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\SlotType;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
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

test('orders list page can be rendered', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->count(3)->create();

    Livewire::test(ListOrders::class)->assertSuccessful();
});

test('the orders list status can be changed inline via the select column', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
    ]);

    Livewire::test(ListOrders::class)
        ->assertSuccessful()
        ->assertTableSelectColumnHasOptions('status', [
            'incomplete' => 'ثبت ناقص سفارش',
            'initial' => 'ثبت اولیه',
            'modified' => 'تغییر یافته',
            'in_queue' => 'در صف تولید',
            'in_production' => 'در حال تولید',
            'ready_to_load' => 'آماده بارگیری',
            'partially_shipped' => 'ارسال جزئی',
            'completely_shipped' => 'کاملاً ارسال شده',
        ], $order);

    $component = Livewire::test(ListOrders::class);

    $component->call('updateTableColumnState', 'status', $order->getKey(), OrderStatus::InProduction->value);

    expect($order->fresh()->status)->toBe(OrderStatus::InProduction);
});

test('the orders list can be filtered by multiple statuses', function () {
    $customer = Customer::factory()->create();
    $inProduction = Order::factory()->for($customer)->create([
        'status' => OrderStatus::InProduction,
    ]);
    $partiallyShipped = Order::factory()->for($customer)->create([
        'status' => OrderStatus::PartiallyShipped,
    ]);
    Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
    ]);

    Livewire::test(ListOrders::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$inProduction, $partiallyShipped])
        ->filterTable('status', [OrderStatus::InProduction->value, OrderStatus::PartiallyShipped->value])
        ->assertCanSeeTableRecords([$inProduction, $partiallyShipped])
        ->assertCanNotSeeTableRecords([$customer->orders()->where('status', OrderStatus::Initial)->first()]);
});

test('completely shipped orders are hidden by default until the filter includes them', function () {
    $customer = Customer::factory()->create();
    $completelyShipped = Order::factory()->for($customer)->create([
        'status' => OrderStatus::CompletelyShipped,
    ]);
    $partiallyShipped = Order::factory()->for($customer)->create([
        'status' => OrderStatus::PartiallyShipped,
    ]);

    Livewire::test(ListOrders::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$partiallyShipped])
        ->assertCanNotSeeTableRecords([$completelyShipped])
        ->filterTable('status', [
            OrderStatus::PartiallyShipped->value,
            OrderStatus::CompletelyShipped->value,
        ])
        ->assertCanSeeTableRecords([$partiallyShipped, $completelyShipped]);
});

test('an order view shows shipment totals and weights', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    $product = Product::factory()->create(['weight' => 1.25]);
    $orderItem = $order->orderItems()->create([
        'product_id' => $product->id,
        'quantity' => 500,
        'color' => 'black',
    ]);

    foreach ([100, 200] as $shippedQuantity) {
        $shipment = Shipment::factory()->for($order)->create();
        ShipmentItem::factory()->create([
            'shipment_id' => $shipment->id,
            'order_item_id' => $orderItem->id,
            'shipped_quantity' => $shippedQuantity,
        ]);
    }

    $shipment = Shipment::factory()->for($order)->create();
    ShipmentItem::factory()->create([
        'shipment_id' => $shipment->id,
        'order_item_id' => $orderItem->id,
        'shipped_quantity' => 50,
    ]);

    Livewire::test(ViewOrder::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee('500')
        ->assertSee('350')
        ->assertSee('150')
        ->assertSee('0.625')
        ->assertDontSee('100 + 200 + 50 دست');
});

test('the order view infolist shows order-level specs', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'color' => 'copper',
        'slot_type' => SlotType::Angled,
        'chamfer_type' => ChamferType::Standard,
    ]);

    Livewire::test(ViewOrder::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee('رنگ')
        ->assertSee('copper')
        ->assertSee('نوع شیار')
        ->assertSee('شیار کج')
        ->assertSee('نوع پخ')
        ->assertSee('پخ دار');
});

test('an order can be created with items via the repeater and receives an order number', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $product = Product::factory()->create();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'brand' => 'Bednar',
            'priority' => OrderPriority::High->value,
            'ordered_at' => now()->toDateString(),
            'payment_notes' => 'Half paid in advance.',
            'color' => 'black',
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'backing_plate_thickness' => 3.5,
                    'has_shim' => true,
                    'color' => 'black',
                    'slot_type' => SlotType::Straight->value,
                    'chamfer_type' => ChamferType::Standard->value,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = Order::sole();

    expect($order->order_number)->toBe('14-001')
        ->and($order->customer_id)->toBe($customer->id)
        ->and($order->priority)->toBe(OrderPriority::High)
        ->and($order->status)->toBe(OrderStatus::Initial)
        ->and($order->orderItems)->toHaveCount(1);

    $item = $order->orderItems->sole();

    expect($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(5)
        ->and($item->has_shim)->toBeTrue()
        ->and($item->color)->toBe('black')
        ->and($item->slot_type)->toBe(SlotType::Straight)
        ->and($item->chamfer_type)->toBe(ChamferType::Standard);
});

test('creating multiple orders for the same customer increments the order number', function () {
    $customer = Customer::factory()->create(['code' => '99']);
    $product = Product::factory()->create();

    foreach (range(1, 2) as $_) {
        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'brand' => 'Bednar',
                'ordered_at' => now()->toDateString(),
                'color' => 'black',
                'orderItems' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'color' => 'grey',
                        'slot_type' => SlotType::None->value,
                        'chamfer_type' => ChamferType::None->value,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    expect(Order::pluck('order_number')->sort()->values()->all())
        ->toBe(['99-001', '99-002']);
});

test('editing an order auto-changes the status to modified when the data changes', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'brand' => 'برند جدید',
            'status' => $order->status->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified);
});

test('manually changing the status is respected and not overridden', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'status' => OrderStatus::InProduction->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::InProduction);
});

test('updating an order item auto-changes the status to modified', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);
    $product = Product::factory()->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 5,
        'color' => 'black',
        'slot_type' => SlotType::Straight,
        'chamfer_type' => ChamferType::Standard,
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'orderItems' => [
                "record-{$orderItem->id}" => [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'color' => 'black',
                    'slot_type' => SlotType::Straight->value,
                    'chamfer_type' => ChamferType::Standard->value,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified)
        ->and($orderItem->fresh()->quantity)->toBe(10);
});

test('adding a new order item auto-changes the status to modified', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);
    $product = Product::factory()->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 5,
        'color' => 'black',
        'slot_type' => SlotType::None,
        'chamfer_type' => ChamferType::None,
    ]);
    $newProduct = Product::factory()->create();

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'orderItems' => [
                "record-{$orderItem->id}" => [
                    'product_id' => $orderItem->product_id,
                    'quantity' => $orderItem->quantity,
                    'color' => $orderItem->color,
                    'slot_type' => $orderItem->slot_type->value,
                    'chamfer_type' => $orderItem->chamfer_type->value,
                ],
                'new-item' => [
                    'product_id' => $newProduct->id,
                    'quantity' => 3,
                    'color' => 'red',
                    'slot_type' => SlotType::Straight->value,
                    'chamfer_type' => ChamferType::Standard->value,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified)
        ->and($order->fresh()->orderItems)->toHaveCount(2);
});

test('removing an order item auto-changes the status to modified', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);
    $product = Product::factory()->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 5,
        'color' => 'black',
        'slot_type' => SlotType::None,
        'chamfer_type' => ChamferType::None,
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'orderItems' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified)
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(0);
});

test('submitting the order form marks the order as modified even without changes', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);
    $product = Product::factory()->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 5,
        'color' => 'black',
        'slot_type' => SlotType::Straight,
        'chamfer_type' => ChamferType::Standard,
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'orderItems' => [
                "record-{$orderItem->id}" => [
                    'product_id' => $product->id,
                    'quantity' => $orderItem->quantity,
                    'color' => $orderItem->color,
                    'slot_type' => $orderItem->slot_type->value,
                    'chamfer_type' => $orderItem->chamfer_type->value,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified);
});

test('editing a single item field like in the browser marks the order as modified', function () {
    $customer = Customer::factory()->create(['code' => '14']);
    $order = Order::factory()->for($customer)->create([
        'status' => OrderStatus::Initial,
        'color' => 'black',
    ]);
    $product = Product::factory()->create();
    $orderItem = OrderItem::factory()->for($order)->for($product)->create([
        'quantity' => 5,
        'color' => 'black',
        'slot_type' => SlotType::None,
        'chamfer_type' => ChamferType::None,
    ]);

    $component = Livewire::test(EditOrder::class, ['record' => $order->id]);

    $items = $component->instance()->data['orderItems'];
    $itemKey = array_key_first($items);

    expect($itemKey)->toBe("record-{$orderItem->id}");

    $component
        ->set("data.orderItems.{$itemKey}.quantity", 12)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Modified)
        ->and($orderItem->fresh()->quantity)->toBe(12);
});

test('order-level specs are saved with the order', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'brand' => 'Brando',
            'color' => 'copper',
            'slot_type' => SlotType::Angled->value,
            'chamfer_type' => ChamferType::Standard->value,
            'ordered_at' => now()->toDateString(),
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'color' => 'black',
                    'slot_type' => SlotType::Straight->value,
                    'chamfer_type' => ChamferType::Standard->value,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = Order::sole();

    expect($order->color)->toBe('copper')
        ->and($order->slot_type)->toBe(SlotType::Angled)
        ->and($order->chamfer_type)->toBe(ChamferType::Standard);
});

test('order-level color is required when saving an order', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'brand' => 'Brando',
            'color' => null,
            'ordered_at' => now()->toDateString(),
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'color' => 'black',
                    'slot_type' => SlotType::None->value,
                    'chamfer_type' => ChamferType::None->value,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['color' => 'required']);
});

test('order item specs are optional and saved as null', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'brand' => 'Brando',
            'color' => 'copper',
            'slot_type' => SlotType::Angled->value,
            'chamfer_type' => ChamferType::Standard->value,
            'ordered_at' => now()->toDateString(),
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'color' => null,
                    'slot_type' => null,
                    'chamfer_type' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $item = Order::sole()->orderItems->sole();

    expect($item->color)->toBeNull()
        ->and($item->slot_type)->toBeNull()
        ->and($item->chamfer_type)->toBeNull();
});
