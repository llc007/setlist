<?php

use App\Models\Banda;
use App\Models\User;
use Livewire\Livewire;

it('permite a un usuario autenticado crear una nueva banda', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::bandas.index')
        ->set('nombre', 'Banda de Prueba')
        ->set('descripcion', 'Descripción de prueba')
        ->call('createBanda')
        ->assertHasNoErrors()
        ->assertRedirect(route('bandas.show', ['banda' => 'banda-de-prueba']));

    $this->assertDatabaseHas('bandas', [
        'nombre' => 'Banda de Prueba',
        'slug' => 'banda-de-prueba',
    ]);

    $banda = Banda::where('slug', 'banda-de-prueba')->first();
    expect($banda->miembros->pluck('id'))->toContain($user->id);

    $pivotRole = $banda->miembros()->where('users.id', $user->id)->first()->pivot->rol;
    expect($pivotRole)->toBe('Administrador de Banda');
});

it('permite agregar e invitar integrantes a una banda', function () {
    $admin = User::factory()->create();
    $integrante = User::factory()->create();

    $banda = Banda::create([
        'nombre' => 'Banda Ejemplo',
        'slug' => 'banda-ejemplo',
    ]);

    $banda->miembros()->attach($admin->id, ['rol' => 'Administrador de Banda']);

    Livewire::actingAs($admin)
        ->test('pages::bandas.miembros', ['banda' => $banda])
        ->set('selectedUserId', $integrante->id)
        ->set('rol', 'Integrante de Banda')
        ->call('addMember')
        ->assertHasNoErrors();

    expect($banda->fresh()->miembros->pluck('id'))->toContain($integrante->id);
});

it('permite quitar un integrante de la banda', function () {
    $admin = User::factory()->create();
    $integrante = User::factory()->create();

    $banda = Banda::create([
        'nombre' => 'Banda Ejemplo 2',
        'slug' => 'banda-ejemplo-2',
    ]);

    $banda->miembros()->attach($admin->id, ['rol' => 'Administrador de Banda']);
    $banda->miembros()->attach($integrante->id, ['rol' => 'Integrante de Banda']);

    Livewire::actingAs($admin)
        ->test('pages::bandas.miembros', ['banda' => $banda])
        ->call('removeMember', $integrante->id);

    expect($banda->fresh()->miembros->pluck('id'))->not->toContain($integrante->id);
});
