<?php

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('SuperAdministrador');
    Role::findOrCreate('Administrador de Banda');
    Role::findOrCreate('Integrante de Banda');
});

test('guest users cannot access user management page', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('unauthorized users without permission cannot access user management page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('super admin can access user management page', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $this->actingAs($superAdmin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Gestión de Usuarios');
});

test('super admin can create a new user and assign roles via SFC', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.users.index')
        ->call('openCreateModal')
        ->set('name', 'Nuevo Músico')
        ->set('email', 'musico@example.com')
        ->set('password', 'password123')
        ->set('selectedRoles', ['Integrante de Banda'])
        ->call('saveUser')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'musico@example.com')->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->name)->toBe('Nuevo Músico')
        ->and($newUser->hasRole('Integrante de Banda'))->toBeTrue();
});

test('super admin can update existing user', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $targetUser = User::factory()->create(['name' => 'Original Name']);

    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.users.index')
        ->call('openEditModal', $targetUser->id)
        ->set('name', 'Updated Name')
        ->set('selectedRoles', ['Administrador de Banda'])
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($targetUser->fresh()->name)->toBe('Updated Name')
        ->and($targetUser->fresh()->hasRole('Administrador de Banda'))->toBeTrue();
});

test('super admin cannot delete own account', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.users.index')
        ->call('confirmDelete', $superAdmin->id)
        ->assertSee('No puedes eliminar tu propio usuario.');
});
