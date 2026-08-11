<?php

use App\Enums\ChamferType;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\PadColor;
use App\Enums\ProductModel;
use App\Enums\SlotType;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('orders list page can be rendered', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->count(3)->create();

    Livewire::test(ListOrders::class)->assertSuccessful();
});

test('an order can be created with items via the repeater and receives an order number', function () {
    $customer = Customer::factory()->create(['code' => '14']);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'priority' => OrderPriority::High->value,
            'status' => OrderStatus::Pending->value,
            'ordered_at' => now()->toDateString(),
            'payment_notes' => 'Half paid in advance.',
            'orderItems' => [
                [
                    'product_model' => ProductModel::Model001->value,
                    'quantity' => 5,
                    'backing_plate_thickness' => 3.5,
                    'has_shim' => true,
                    'color' => PadColor::Black->value,
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
        ->and($order->orderItems)->toHaveCount(1);

    $item = $order->orderItems->sole();

    expect($item->product_model)->toBe(ProductModel::Model001)
        ->and($item->quantity)->toBe(5)
        ->and($item->has_shim)->toBeTrue()
        ->and($item->color)->toBe(PadColor::Black)
        ->and($item->slot_type)->toBe(SlotType::Straight)
        ->and($item->chamfer_type)->toBe(ChamferType::Standard);
});

test('creating multiple orders for the same customer increments the order number', function () {
    $customer = Customer::factory()->create(['code' => '99']);

    foreach (range(1, 2) as $_) {
        Livewire::test(CreateOrder::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'ordered_at' => now()->toDateString(),
                'orderItems' => [
                    [
                        'product_model' => ProductModel::Model002->value,
                        'quantity' => 1,
                        'color' => PadColor::Grey->value,
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
