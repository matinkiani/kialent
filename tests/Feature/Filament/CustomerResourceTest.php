<?php

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(asSuperAdmin());
});

test('customers list page can be rendered', function () {
    Customer::factory()->count(3)->create();

    Livewire::test(ListCustomers::class)->assertSuccessful();
});

test('a customer can be created with an uppercased code', function () {
    Livewire::test(CreateCustomer::class)
        ->fillForm([
            'name' => 'Al Motors',
            'code' => 'al',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $customer = Customer::sole();

    expect($customer->name)->toBe('Al Motors')
        ->and($customer->code)->toBe('AL')
        ->and($customer->next_order_sequence)->toBe(1);
});

test('a customer code must be unique', function () {
    Customer::factory()->create(['code' => 'AL']);

    Livewire::test(CreateCustomer::class)
        ->fillForm([
            'name' => 'Another Customer',
            'code' => 'AL',
        ])
        ->call('create')
        ->assertHasFormErrors(['code' => 'unique']);
});
