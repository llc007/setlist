<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('roles and permissions are seeded correctly', function () {
    expect(Role::where('name', 'SuperAdministrador')->exists())->toBeTrue();
    expect(Role::where('name', 'Administrador de Banda')->exists())->toBeTrue();
    expect(Role::where('name', 'Integrante de Banda')->exists())->toBeTrue();

    expect(Permission::where('name', 'gestionar-usuarios')->exists())->toBeTrue();
    expect(Permission::where('name', 'gestionar-roles')->exists())->toBeTrue();
    expect(Permission::where('name', 'gestionar-canciones')->exists())->toBeTrue();
    expect(Permission::where('name', 'ver-repertorio')->exists())->toBeTrue();
});

test('user can be assigned SuperAdministrador role and check permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('SuperAdministrador');

    expect($user->hasRole('SuperAdministrador'))->toBeTrue();
    expect($user->hasPermissionTo('gestionar-usuarios'))->toBeTrue();
});

test('admin route can be accessed by user with SuperAdministrador role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('SuperAdministrador');

    $response = $this->actingAs($admin)->get(route('admin'));

    $response->assertSuccessful();
});

test('admin route denies access to user without admin role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin'));

    $response->assertForbidden();
});
