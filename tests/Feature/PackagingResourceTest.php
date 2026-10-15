<?php

use App\Filament\Resources\Packagings\Pages\CreatePackaging;
use App\Models\Packaging;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(asSuperAdmin());
});

test('a packaging can define different quantities for its products', function () {
    $firstProduct = Product::factory()->create(['name' => 'Product One']);
    $secondProduct = Product::factory()->create(['name' => 'Product Two']);

    Livewire::test(CreatePackaging::class)
        ->fillForm([
            'name' => 'Box Foo',
            'brand_name' => 'Brand Foo',
            'packagingItems' => [
                ['product_id' => $firstProduct->id, 'quantity' => 3],
                ['product_id' => $secondProduct->id, 'quantity' => 5],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $packaging = Packaging::with('packagingItems')->sole();

    expect($packaging->name)->toBe('Box Foo')
        ->and($packaging->brand_name)->toBe('Brand Foo')
        ->and($packaging->packagingItems)->toHaveCount(2)
        ->and($packaging->packagingItems->pluck('quantity', 'product_id')->all())
        ->toBe([
            $firstProduct->id => 3,
            $secondProduct->id => 5,
        ]);
});
