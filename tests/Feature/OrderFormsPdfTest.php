<?php

use App\Enums\ChamferType;
use App\Enums\OrderFormType;
use App\Enums\SlotType;
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

test('the production form shows order-level specs instead of per-item spec columns', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'color' => 'copper',
        'slot_type' => SlotType::Angled,
        'chamfer_type' => ChamferType::Standard,
    ]);
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'color' => 'black',
        'slot_type' => SlotType::None,
        'chamfer_type' => ChamferType::None,
        'backing_plate_thickness' => 9.5,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['production'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('مشخصات فنی سفارش')
        ->toContain('copper')
        ->toContain('شیار کج')
        ->toContain('پخ دار')
        ->toContain('پخت شده')
        ->toContain('کوره رفته')
        ->toContain('رنگ شده')
        ->not->toContain('کفشک')
        ->not->toContain('بدون شیار')
        ->not->toContain('بدون پخ')
        ->not->toContain('9.5');
});

test('the management form shows order-level specs instead of per-item spec columns', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'color' => 'silver',
        'slot_type' => SlotType::Straight,
        'chamfer_type' => ChamferType::None,
    ]);
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'color' => 'black',
        'slot_type' => SlotType::Angled,
        'chamfer_type' => ChamferType::Standard,
        'backing_plate_thickness' => 9.5,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['management'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('مشخصات فنی سفارش')
        ->toContain('silver')
        ->toContain('شیار صاف')
        ->toContain('بدون پخ')
        ->not->toContain('کفشک')
        ->not->toContain('شیار کج')
        ->not->toContain('پخ دار')
        ->not->toContain('9.5');
});

test('the packaging form shows order-level specs and only filled item specs with the production note', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'color' => 'copper',
        'slot_type' => SlotType::None,
        'chamfer_type' => ChamferType::None,
    ]);
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'color' => 'black',
        'slot_type' => null,
        'chamfer_type' => ChamferType::Standard,
        'backing_plate_thickness' => null,
        'production_notes' => 'نکته تولیدی تستی',
        'packaging_notes' => null,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['packaging'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('مشخصات فنی سفارش')
        ->toContain('copper')
        ->toContain('پخ دار')
        ->toContain('>تولید:</span>')
        ->toContain('نکته تولیدی تستی')
        ->not->toContain('کفشک:')
        ->not->toContain('شیار:</span>');
});

test('the delivery form combines production and packaging notes in the توضیحات column', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'production_notes' => 'یادداشت تولید',
        'packaging_notes' => 'یادداشت بسته‌بندی',
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['delivery'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('مشخصات فنی سفارش')
        ->toContain('>تولید:</span>')
        ->toContain('یادداشت تولید')
        ->toContain('>بسته‌بندی:</span>')
        ->toContain('یادداشت بسته‌بندی')
        ->not->toContain('توضیحات بسته‌بندی');
});

test('the delivery form writes a single note without a label', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'production_notes' => 'یادداشت تولید',
        'packaging_notes' => null,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['delivery'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('یادداشت تولید')
        ->not->toContain('>تولید:</span>')
        ->not->toContain('>بسته‌بندی:</span>');
});

test('the delivery form keeps the توضیحات cell empty when both notes are null', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    OrderItem::factory()->for($order)->for(Product::factory()->create())->create([
        'quantity' => 10,
        'production_notes' => null,
        'packaging_notes' => null,
    ]);

    $html = $this->get(route('orders.forms.download', [
        'order' => $order,
        'forms' => ['delivery'],
    ]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->not->toContain('>تولید:</span>')
        ->not->toContain('>بسته‌بندی:</span>');
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
