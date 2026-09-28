<?php

use App\Http\Controllers\Admin\DasboardController;
use App\Http\Controllers\Auth\ProviderController;
use App\Models\Banda;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/auth/{provider}/redirect', [ProviderController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [ProviderController::class, 'callback'])->name('socialite.callback');

// Vista pública compartible del Setlist (para enlaces de WhatsApp sin requerir inicio de sesión)
Route::livewire('/setlist/{setlist}', 'pages::setlists.public-show')->name('setlists.public');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// admin dashboard
Route::get('admin', [DasboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin');

Route::livewire('admin/canciones', 'pages::admin.canciones.index')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.canciones');

Route::livewire('admin/users', 'pages::admin.users.index')
    ->middleware(['auth', 'verified', 'can:gestionar-usuarios'])
    ->name('admin.users.index');

Route::livewire('admin/roles', 'pages::admin.roles.index')
    ->middleware(['auth', 'verified', 'can:gestionar-roles'])
    ->name('admin.roles.index');

Route::livewire('admin/categorias', 'pages::admin.categorias.index')
    ->middleware(['auth', 'verified', 'can:gestionar-categorias'])
    ->name('admin.categorias.index');

Route::livewire('admin/bandas', 'pages::admin.bandas.index')
    ->middleware(['auth', 'verified', 'can:gestionar-bandas'])
    ->name('admin.bandas.index');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
    Route::livewire('settings/password', 'pages::settings.password')->name('user-password.edit');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
    Route::livewire('/repertorio/{cancion}', 'pages::repertorio.show')->name('ver-cancion');
    Route::livewire('/repertorio/{cancion}/imprimir', 'pages::repertorio.imprimir')->name('canciones.imprimir');

    // Ruta para revisar el repertorio publico
    Route::livewire('/repertorio', 'pages::repertorio.index')->name('repertorio');

    // Rutas del Módulo de Bandas y Setlists
    Route::get('/setlists', function () {
        $activeBandaId = session('active_banda_id');
        $banda = $activeBandaId ? Banda::find($activeBandaId) : null;
        if (! $banda) {
            $banda = auth()->user()->bandas()->first() ?? Banda::first();
        }
        if ($banda) {
            return redirect()->route('bandas.setlists.index', $banda->slug);
        }

        return redirect()->route('bandas.index');
    })->name('setlists.index');

    Route::livewire('/bandas', 'pages::bandas.index')->name('bandas.index');
    Route::livewire('/bandas/{banda:slug}', 'pages::bandas.show')->name('bandas.show');
    Route::livewire('/bandas/{banda:slug}/configuracion', 'pages::bandas.configuracion')->name('bandas.configuracion');
    Route::livewire('/bandas/{banda:slug}/repertorio', 'pages::bandas.repertorio.index')->name('bandas.repertorio.index');
    Route::livewire('/bandas/{banda:slug}/setlists', 'pages::bandas.setlists.index')->name('bandas.setlists.index');
    Route::livewire('/bandas/{banda:slug}/setlists/{setlist}', 'pages::bandas.setlists.show')->name('bandas.setlists.show');
    Route::livewire('/bandas/{banda:slug}/miembros', 'pages::bandas.miembros')->name('bandas.miembros');

    Route::livewire('settings/two-factor', 'pages::settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
