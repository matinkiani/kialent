<?php

use App\Enums\BackPlateType;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('a product can be created with a weight', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Weighted Product',
            'weight' => '12.345',
            'back_plate_type' => BackPlateType::OneType->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::query()->firstOrFail()->weight)->toBe('12.345');
});
