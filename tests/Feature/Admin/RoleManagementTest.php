<?php

use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('SuperAdministrador');
    Role::findOrCreate('Administrador de Banda');
    Role::findOrCreate('Integrante de Banda');
    Permission::findOrCreate('gestionar-canciones');
    Permission::findOrCreate('ver-repertorio');
});

test('guest users cannot access role management page', function () {
    $this->get(route('admin.roles.index'))
        ->assertRedirect(route('login'));
});

test('super admin can access role management page', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $this->actingAs($superAdmin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Gestión de Roles y Permisos');
});

test('super admin can create a new role and assign permissions via SFC', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.roles.index')
        ->call('openCreateModal')
        ->set('name', 'Director Creativo')
        ->set('selectedPermissions', ['gestionar-canciones', 'ver-repertorio'])
        ->call('saveRole')
        ->assertHasNoErrors();

    $newRole = Role::findByName('Director Creativo');
    expect($newRole)->not->toBeNull()
        ->and($newRole->hasPermissionTo('gestionar-canciones'))->toBeTrue();
});

test('super admin cannot delete SuperAdministrador role', function () {
    $superAdmin = User::factory()->create(['email' => 'llc007.1@gmail.com']);
    $superAdmin->assignRole('SuperAdministrador');

    $superAdminRole = Role::findByName('SuperAdministrador');

    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.roles.index')
        ->call('confirmDelete', $superAdminRole->id)
        ->assertSee('No se puede eliminar el rol SuperAdministrador.');
});
