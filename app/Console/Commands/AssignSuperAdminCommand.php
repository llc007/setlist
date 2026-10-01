<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-superadmin {email? : Correo electrónico del usuario a promover}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna el rol SuperAdministrador a un usuario mediante su correo electrónico';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?? '');

        if ($email === '' && $this->input->isInteractive()) {
            $email = (string) $this->ask('¿Cuál es el correo electrónico del usuario?');
        }

        if ($email === '') {
            $defaultSuperAdmin = config('auth.superadmins')[0] ?? null;
            if ($defaultSuperAdmin) {
                $email = $defaultSuperAdmin;
                $this->info("Usando correo por defecto de configuración: {$email}");
            }
        }

        if ($email === '') {
            $this->error('Debes proporcionar un correo electrónico válido.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No se encontró ningún usuario con el correo: {$email}");
            $this->line('Asegúrate de que el usuario haya iniciado sesión o se haya registrado primero en el sistema.');

            return self::FAILURE;
        }

        $superAdminRole = Role::findOrCreate('SuperAdministrador');

        $permissions = [
            'gestionar-usuarios',
            'gestionar-roles',
            'gestionar-canciones',
            'gestionar-categorias',
            'gestionar-bandas',
            'ver-repertorio',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::findOrCreate($permissionName);
            $superAdminRole->givePermissionTo($permission);
        }

        $user->assignRole($superAdminRole);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("¡Éxito! El usuario {$user->name} ({$user->email}) ahora tiene el rol SuperAdministrador y todos sus permisos.");

        return self::SUCCESS;
    }
}
