<?php

use App\Enums\OrderStatus;
use App\Filament\Widgets\MonthlyOrdersChart;
use App\Filament\Widgets\OrdersByCustomerChart;
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\OrderStatusChart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\JalaliPeriod;
use App\Support\OrderQuantities;
use Ariaieboy\Jalali\Jalali;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->actingAs(asSuperAdmin());
});

function createOrderWithQuantity(Customer $customer, mixed $orderedAt, int $quantity): Order
{
    $order = Order::factory()->for($customer)->create(['ordered_at' => $orderedAt]);

    OrderItem::factory()->for($order)->create(['quantity' => $quantity]);

    return $order;
}

test('order quantities are aggregated per period, customer and jalali month', function () {
    $alpha = Customer::factory()->create(['name' => 'Alpha']);
    $beta = Customer::factory()->create(['name' => 'Beta']);

    $yearStart = JalaliPeriod::yearStart();
    $previousMonthStart = JalaliPeriod::previousMonthStart();
    $monthStart = JalaliPeriod::monthStart();

    createOrderWithQuantity($alpha, $monthStart, 120);
    createOrderWithQuantity($beta, $previousMonthStart, 45);
    createOrderWithQuantity($alpha, $yearStart->subDays(10), 99);

    $previousMonthIsInYear = Jalali::forge($previousMonthStart)->getYear() === JalaliPeriod::currentYearNumber();
    $yearQuantity = $previousMonthIsInYear ? 165 : 120;

    expect(OrderQuantities::totalQuantityBetween($monthStart))->toBe(120)
        ->and(OrderQuantities::totalQuantityBetween($previousMonthStart, $monthStart))->toBe(45)
        ->and(OrderQuantities::totalQuantityBetween($yearStart))->toBe($yearQuantity)
        ->and(OrderQuantities::totalQuantityByCustomerSince($yearStart)->all())->toBe(
            $previousMonthIsInYear ? ['Alpha' => 120, 'Beta' => 45] : ['Alpha' => 120],
        );

    $byMonth = OrderQuantities::totalQuantityByJalaliMonthSince($yearStart);

    expect($byMonth[Jalali::forge($monthStart)->getMonth() - 1])->toBe(120)
        ->and(array_sum($byMonth))->toBe($yearQuantity);

    if ($previousMonthIsInYear) {
        expect($byMonth[Jalali::forge($previousMonthStart)->getMonth() - 1])->toBe(45);
    }
});

test('stats overview shows this month and this year quantities in hand', function () {
    $alpha = Customer::factory()->create();
    $beta = Customer::factory()->create();

    $previousMonthIsInYear = Jalali::forge(JalaliPeriod::previousMonthStart())->getYear() === JalaliPeriod::currentYearNumber();
    $yearQuantity = $previousMonthIsInYear ? 165 : 120;

    createOrderWithQuantity($alpha, JalaliPeriod::monthStart(), 120);
    createOrderWithQuantity($beta, JalaliPeriod::previousMonthStart(), 45);
    createOrderWithQuantity($alpha, JalaliPeriod::yearStart()->subDays(10), 99);

    Livewire::test(OrderStatsOverview::class)
        ->assertSuccessful()
        ->assertSee('محصول این ماه (دست)')
        ->assertSee('محصول امسال (دست)')
        ->assertSeeHtml('120')
        ->assertSeeHtml((string) $yearQuantity);
});

test('orders by customer chart shows hand quantity per customer with distinct colors', function () {
    $alpha = Customer::factory()->create(['name' => 'Alpha']);
    $beta = Customer::factory()->create(['name' => 'Beta']);

    createOrderWithQuantity($alpha, JalaliPeriod::monthStart(), 120);
    createOrderWithQuantity($beta, JalaliPeriod::yearStart(), 45);

    Livewire::test(OrdersByCustomerChart::class)
        ->assertSuccessful()
        ->assertSee('indexAxis')
        ->assertSee('[120,45]')
        ->assertSee('#6366f1')
        ->assertSee('#f43f5e');
});

test('monthly orders chart shows hand quantity per jalali month with intensity colors', function () {
    createOrderWithQuantity(Customer::factory()->create(), JalaliPeriod::monthStart(), 120);
    createOrderWithQuantity(Customer::factory()->create(), JalaliPeriod::yearStart(), 45);

    $expected = array_fill(0, 12, 0);
    $expected[JalaliPeriod::currentMonthNumber() - 1] += 120;
    $expected[0] += 45;

    Livewire::test(MonthlyOrdersChart::class)
        ->assertSuccessful()
        ->assertSee('hsl(243')
        ->assertSee(json_encode($expected));
});

test('order status chart colors each status slice', function () {
    $customer = Customer::factory()->create();

    createOrderWithQuantity($customer, JalaliPeriod::monthStart(), 1);

    $shipped = Order::factory()->for($customer)->create([
        'ordered_at' => JalaliPeriod::monthStart(),
        'status' => OrderStatus::CompletelyShipped,
    ]);
    OrderItem::factory()->for($shipped)->create(['quantity' => 1]);

    Livewire::test(OrderStatusChart::class)
        ->assertSuccessful()
        ->assertSee('#60a5fa')
        ->assertSee('#34d399');
});

test('users without the widget permission cannot see dashboard widgets', function () {
    $role = Role::create(['name' => 'NoWidgets']);
    $user = User::factory()->create();
    $user->assignRole($role);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSuccessful()
        ->assertDontSee('آمار سفارش‌ها');

    expect(MonthlyOrdersChart::canView())->toBeFalse();
});

test('users with the widget permission see dashboard widgets', function () {
    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertSee('آمار سفارش‌ها');
});

test('the dashboard does not show the record activity timeline', function () {
    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertDontSee('تاریخچه تغییرات');
});
