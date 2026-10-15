<?php

use App\Enums\ChamferType;
use App\Enums\SlotType;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Packaging;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(asSuperAdmin());
});

test('packaging boxes are converted to hands and persisted alongside the box count', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $packaging = Packaging::factory()->create(['brand_name' => 'Box Brand']);
    $packaging->packagingItems()->create([
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'packaging_id' => $packaging->id,
            'brand' => 'Box Brand',
            'color' => 'black',
            'ordered_at' => now()->toDateString(),
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 6,
                    'box_quantity' => 2,
                    'color' => 'black',
                    'slot_type' => SlotType::None->value,
                    'chamfer_type' => ChamferType::None->value,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = Order::with('orderItems')->sole();
    $item = $order->orderItems->sole();

    expect($order->brand)->toBe('Box Brand')
        ->and($order->orderItems)->toHaveCount(1)
        ->and($item->quantity)->toBe(6)
        ->and($item->box_quantity)->toBe(2);
});

test('packaging boxes are converted when a previous submission had validation errors', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    $packaging = Packaging::factory()->create(['brand_name' => 'Box Brand']);
    $packaging->packagingItems()->create([
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $component = Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'packaging_id' => $packaging->id,
            'brand' => null,
            'color' => 'black',
            'ordered_at' => now()->toDateString(),
            'orderItems' => [
                [
                    'product_id' => $product->id,
                    'quantity' => null,
                    'box_quantity' => 2,
                    'color' => 'black',
                    'slot_type' => SlotType::None->value,
                    'chamfer_type' => ChamferType::None->value,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['brand' => 'required']);

    $component
        ->fillForm(['brand' => 'Box Brand'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Order::with('orderItems')->sole()->orderItems->sole()->quantity)->toBe(6);
});
