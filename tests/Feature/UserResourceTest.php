<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $superAdminRole = Role::create(['name' => 'super_admin']);
    $superAdminRole->givePermissionTo([
        Permission::create(['name' => 'ViewAny:User']),
        Permission::create(['name' => 'Create:User']),
        Permission::create(['name' => 'Update:User']),
        Permission::create(['name' => 'Delete:User']),
    ]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super_admin');
});

test('super admin can create a user with a role', function () {
    $staffRole = Role::create(['name' => 'Staff']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'Limited User',
            'email' => 'limited@example.com',
            'password' => 'secret1234',
            'passwordConfirmation' => 'secret1234',
            'roles' => [$staffRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'name' => 'Limited User',
        'email' => 'limited@example.com',
    ]);

    $user = User::whereEmail('limited@example.com')->firstOrFail();

    expect($user->hasRole('Staff'))->toBeTrue()
        ->and(Hash::check('secret1234', $user->password))->toBeTrue();
});

test('staff role cannot access user management', function () {
    $staffRole = Role::create(['name' => 'Staff']);

    $staff = User::factory()->create();
    $staff->assignRole($staffRole);

    Livewire::actingAs($staff)
        ->test(ListUsers::class)
        ->assertForbidden();
});

test('staff role can view products but not create them', function () {
    $staffRole = Role::create(['name' => 'Staff']);
    $staffRole->givePermissionTo([
        Permission::create(['name' => 'ViewAny:Product']),
        Permission::create(['name' => 'View:Product']),
    ]);

    $staff = User::factory()->create();
    $staff->assignRole($staffRole);

    $product = Product::factory()->create();

    Livewire::actingAs($staff)
        ->test(ListProducts::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$product]);

    Livewire::actingAs($staff)
        ->test(CreateProduct::class)
        ->assertForbidden();
});

test('user without a role cannot access the panel resources', function () {
    $nobody = User::factory()->create();

    Livewire::actingAs($nobody)
        ->test(ListProducts::class)
        ->assertForbidden();
});
