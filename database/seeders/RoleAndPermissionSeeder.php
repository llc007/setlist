<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $gestionarUsuarios = Permission::findOrCreate('gestionar-usuarios');
        $gestionarRoles = Permission::findOrCreate('gestionar-roles');
        $gestionarCanciones = Permission::findOrCreate('gestionar-canciones');
        $verRepertorio = Permission::findOrCreate('ver-repertorio');

        // Create roles and assign permissions
        $superAdminRole = Role::findOrCreate('SuperAdministrador');
        $superAdminRole->givePermissionTo([
            $gestionarUsuarios,
            $gestionarRoles,
            $gestionarCanciones,
            $verRepertorio,
        ]);

        $adminBandaRole = Role::findOrCreate('Administrador de Banda');
        $adminBandaRole->givePermissionTo([
            $gestionarCanciones,
            $verRepertorio,
        ]);

        $integranteBandaRole = Role::findOrCreate('Integrante de Banda');
        $integranteBandaRole->givePermissionTo([
            $verRepertorio,
        ]);

        // Legacy roles support if needed
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo([$gestionarCanciones, $verRepertorio]);

        // Assign SuperAdministrador role to llc007.1@gmail.com
        $superAdminUser = User::where('email', 'llc007.1@gmail.com')->first();
        if ($superAdminUser) {
            $superAdminUser->assignRole($superAdminRole);
        }
    }
}
