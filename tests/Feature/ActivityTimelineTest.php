<?php

use App\Filament\Pages\ActivityHistory;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrderActivities;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Packagings\Pages\ListPackagingActivities;
use App\Filament\Resources\Users\Pages\ListUserActivities;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Packaging;
use App\Models\PackagingItem;
use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = asSuperAdmin();
    $this->actingAs($this->superAdmin);
});

test('editing an order records who changed it with old and new values', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create([
        'color' => 'سفید',
    ]);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->fillForm(['color' => 'مشکی'])
        ->call('save')
        ->assertHasNoFormErrors();

    $activity = Activity::query()
        ->where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('description', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($activity->causer_id)->toBe(auth()->id())
        ->and($activity->attribute_changes['old']['color'])->toBe('سفید')
        ->and($activity->attribute_changes['attributes']['color'])->toBe('مشکی');
});

test('the order activities page shows who changed what with old and new values', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create([
        'color' => 'سفید',
    ]);

    $order->update(['color' => 'مشکی']);

    Livewire::test(ListOrderActivities::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('به‌روزرسانی شد')
        ->assertSee('رنگ')
        ->assertSee('سفید')
        ->assertSee('مشکی');
});

test('the user activities page lists changes caused by the user', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();

    Livewire::test(ListUserActivities::class, ['record' => $this->superAdmin->id])
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('ایجاد شد');
});

test('order item changes appear in the order activities page', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();
    $item = OrderItem::factory()->for($order)->create(['color' => 'آبی-منحصر']);

    $item->update(['color' => 'سرخابی-منحصر', 'quantity' => 9]);

    Livewire::test(ListOrderActivities::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee('آبی-منحصر')
        ->assertSee('سرخابی-منحصر');
});

test('deleted order items remain visible in the order activities page', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();
    $item = OrderItem::factory()->for($order)->create(['color' => 'زرد-منحصر']);

    $item->delete();

    Livewire::test(ListOrderActivities::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee('زرد-منحصر')
        ->assertSee('حذف شد');
});

test('restoring an item activity restores the item not the order', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create(['color' => 'قرمز']);
    $item = OrderItem::factory()->for($order)->create(['quantity' => 4]);

    $item->update(['quantity' => 9]);

    $activity = Activity::query()
        ->where('subject_type', OrderItem::class)
        ->where('subject_id', $item->id)
        ->where('description', 'updated')
        ->latest('id')
        ->firstOrFail();

    Livewire::test(ListOrderActivities::class, ['record' => $order->id])
        ->call('restoreActivity', $activity->id);

    expect($item->fresh()->quantity)->toBe(4)
        ->and($order->fresh()->color)->toBe('قرمز');
});

test('packaging item changes appear in the packaging activities page', function () {
    $packaging = Packaging::factory()->create();
    $item = PackagingItem::factory()->for($packaging)->create(['quantity' => 3]);

    $item->update(['quantity' => 7]);

    Livewire::test(ListPackagingActivities::class, ['record' => $packaging->id])
        ->assertSuccessful()
        ->assertSee('7')
        ->assertSee('3');
});

test('the activity history permission appears in the shield role editor', function () {
    Livewire::test(CreateRole::class)
        ->assertSuccessful()
        ->assertSee('تاریخچه تغییرات');
});

test('the global activity history page lists all changes', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();

    $order->update(['color' => 'بنفش-منحصر']);

    Livewire::test(ActivityHistory::class)
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('به‌روزرسانی شد')
        ->assertSee('مشتری');
});

test('the global activity history page can be filtered by causer', function () {
    $otherAdmin = asSuperAdmin();
    $order = Order::factory()->for(Customer::factory()->create())->create();

    Livewire::test(ActivityHistory::class)
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->set('tableFilters', ['causer' => ['value' => $otherAdmin->id]])
        ->assertDontSee($order->order_number);

    $this->superAdmin->activitiesAsCauser()->delete();

    Livewire::test(ActivityHistory::class)
        ->assertSuccessful()
        ->assertDontSee($order->order_number);
});

test('the changes modal shows old and new values with Persian labels', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();

    $order->update(['color' => 'قهوه‌ای-منحصر']);

    $activity = Activity::query()
        ->where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('description', 'updated')
        ->latest('id')
        ->firstOrFail();

    $html = view('filament.activity-changes', [
        'activity' => $activity,
        'labels' => (new ActivityHistory)->fieldLabelsFor(Order::class),
    ])->render();

    expect($html)->toContain('رنگ')->toContain('قهوه‌ای-منحصر');
});

test('users without the activity history permission cannot access it', function () {
    $role = Role::create(['name' => 'Viewer']);
    $role->givePermissionTo([
        Permission::create(['name' => 'ViewAny:Order']),
        Permission::create(['name' => 'View:Order']),
    ]);
    $user = User::factory()->create();
    $user->assignRole($role);

    $order = Order::factory()->for(Customer::factory()->create())->create();

    Livewire::actingAs($user)
        ->test(ListOrderActivities::class, ['record' => $order->id])
        ->assertForbidden();

    Livewire::actingAs($user)
        ->test(ViewOrder::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertDontSee('تاریخچه تغییرات');
});

test('users with the activity history permission can access it', function () {
    $role = Role::create(['name' => 'Auditor']);
    $role->givePermissionTo([
        Permission::create(['name' => 'ViewAny:Order']),
        Permission::create(['name' => 'View:Order']),
        Permission::findOrCreate('View:ActivityHistory', 'web'),
    ]);
    $user = User::factory()->create();
    $user->assignRole($role);

    $order = Order::factory()->for(Customer::factory()->create())->create();

    Livewire::actingAs($user)
        ->test(ListOrderActivities::class, ['record' => $order->id])
        ->assertSuccessful();

    Livewire::actingAs($user)
        ->test(ViewOrder::class, ['record' => $order->id])
        ->assertSuccessful()
        ->assertSee('تاریخچه تغییرات');
});

test('orders with no changes do not log empty activities', function () {
    $order = Order::factory()->for(Customer::factory()->create())->create();

    $updatesBefore = Activity::query()
        ->where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('description', 'updated')
        ->count();

    Order::query()->whereKey($order->id)->update(['payment_notes' => $order->payment_notes]);

    $updatesAfter = Activity::query()
        ->where('subject_type', Order::class)
        ->where('subject_id', $order->id)
        ->where('description', 'updated')
        ->count();

    expect($updatesAfter)->toBe($updatesBefore);
});

test('password changes are never logged on users', function () {
    $user = User::factory()->create();

    $user->update(['password' => 'new-secret-password', 'name' => 'نام جدید']);

    $activities = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->get();

    expect($activities)->not->toBeEmpty()
        ->and($activities->flatMap(fn (Activity $activity) => array_merge(
            $activity->attribute_changes['attributes'] ?? [],
            $activity->attribute_changes['old'] ?? [],
        ))->keys())->each(fn ($key) => expect($key->value)->not->toBe('password'));
});
