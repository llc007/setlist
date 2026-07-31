<?php

use App\Http\Controllers\Admin\DasboardController;
use App\Http\Controllers\Auth\ProviderController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/auth/{provider}/redirect', [ProviderController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [ProviderController::class, 'callback'])->name('socialite.callback');

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

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
    Route::livewire('settings/password', 'pages::settings.password')->name('user-password.edit');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
    Route::livewire('/repertorio/{cancion}', 'pages::repertorio.show')->name('ver-cancion');

    // Ruta para revisar el repertorio publico
    Route::livewire('/repertorio', 'pages::repertorio.index')->name('repertorio');

    // Rutas del Módulo de Bandas y Setlists
    Route::livewire('/bandas', 'pages::bandas.index')->name('bandas.index');
    Route::livewire('/bandas/{banda:slug}', 'pages::bandas.show')->name('bandas.show');
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
