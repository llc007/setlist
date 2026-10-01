<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Si el correo ya existe, lo actualizamos con los datos del proveedor
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                ]);
            } else {
                // Si el correo no existe, creamos un nuevo usuario
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => null,
                ]);
            }

            // Si el correo está configurado como superadmin, asignar rol automáticamente
            $superadminEmails = config('auth.superadmins', []);
            if (in_array($user->email, $superadminEmails, true) && ! $user->hasRole('SuperAdministrador')) {
                $role = Role::findOrCreate('SuperAdministrador');
                $user->assignRole($role);
            }

            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            return redirect(route('login'))->with('status', 'Hubo un error al iniciar sesión con '.ucfirst($provider));
        }
    }
}
