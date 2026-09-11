<?php

use App\Enums\OrderFormType;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('the combined forms route renders only the selected forms in separate sheets', function () {
    $customer = Customer::factory()->create(['name' => 'پارس ریسینگ']);
    $order = Order::factory()->for($customer)->create();
    OrderItem::factory()->for($order)->for(Product::factory()->create(['name' => 'لنت پژو ۴۰۵']))->create([
        'quantity' => 10,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['packaging', 'production'],
    ]))
        ->assertOk()
        ->getContent();

    // هر فرم انتخابی در یک کاغذ جداگانه رندر می‌شود
    expect(substr_count($html, 'class="a4-preview sheet'))->toBe(2)
        ->and($html)
        ->toContain('دستورکار بسته‌بندی')
        ->toContain('دستورکار خط تولید')
        ->toContain('پارس ریسینگ')
        ->not->toContain('فرم تحویل به بسته‌بندی')
        ->not->toContain('فرم جامع سفارش');
});

test('the combined forms route rejects requests without any form selection', function () {
    $order = Order::factory()->create();

    $this->get(route('orders.forms.download', $order))->assertStatus(422);
});

test('unknown form keys are ignored silently', function () {
    $order = Order::factory()->create();

    $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['packaging', 'not-a-real-form'],
    ]))
        ->assertOk()
        ->getContent();
});

test('the edit page exposes a single download action with all four form options', function () {
    $order = Order::factory()->create();

    $expectedOptions = collect(OrderFormType::cases())
        ->mapWithKeys(fn (OrderFormType $form): array => [$form->value => $form->getLabel()])
        ->all();

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->mountAction('downloadForms')
        ->assertActionMounted('downloadForms')
        ->assertFormFieldExists('forms', function (CheckboxList $field) use ($expectedOptions): bool {
            return $field->getOptions() === $expectedOptions;
        });
});

test('the download action requires at least one form to be selected', function () {
    $order = Order::factory()->create();

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->callAction('downloadForms', ['forms' => []])
        ->assertHasActionErrors(['forms' => ['required']]);
});

test('the download action accepts multiple selected forms', function () {
    $order = Order::factory()->create();

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->callAction('downloadForms', ['forms' => ['packaging', 'delivery', 'management']])
        ->assertHasNoActionErrors();
});
