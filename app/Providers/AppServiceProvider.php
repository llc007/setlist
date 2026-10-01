<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant superadministrators all permissions
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // Automatically assign SuperAdministrador role on login for configured superadmin emails
        Event::listen(Login::class, function (Login $event): void {
            $superadminEmails = config('auth.superadmins', []);
            if ($event->user && in_array($event->user->email, $superadminEmails, true)) {
                if (! $event->user->hasRole('SuperAdministrador')) {
                    $role = Role::findOrCreate('SuperAdministrador');
                    $event->user->assignRole($role);
                }
            }
        });
    }
}
