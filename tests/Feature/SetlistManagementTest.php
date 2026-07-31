<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Setlist;
use App\Models\User;
use Livewire\Livewire;

it('permite agregar canciones públicas y privadas al repertorio de la banda', function () {
    $user = User::factory()->create();
    $banda = Banda::create(['nombre' => 'Banda de Alabanza', 'slug' => 'banda-alabanza']);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    Livewire::actingAs($user)
        ->test('pages::bandas.repertorio.index', ['banda' => $banda])
        ->set('titulo', 'Cancion Privada de Banda')
        ->set('es_publica', false)
        ->call('saveCancion')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('canciones', [
        'titulo' => 'Cancion Privada de Banda',
        'es_publica' => false,
        'banda_id' => $banda->id,
    ]);

    expect($banda->fresh()->repertorio->pluck('titulo'))->toContain('Cancion Privada de Banda');
});

it('permite crear un setlist para la banda y agregar canciones ordenadas', function () {
    $user = User::factory()->create();
    $banda = Banda::create(['nombre' => 'Banda de Alabanza 2', 'slug' => 'banda-alabanza-2']);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $cancion = Cancion::create([
        'titulo' => 'Oceans',
        'banda_id' => $banda->id,
        'es_publica' => true,
    ]);
    $banda->repertorio()->attach($cancion->id);

    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.index', ['banda' => $banda])
        ->set('nombre', 'Culto Dominical')
        ->set('fecha', now()->format('Y-m-d'))
        ->set('tipo', 'culto')
        ->call('createSetlist')
        ->assertHasNoErrors();

    $setlist = Setlist::where('nombre', 'Culto Dominical')->first();
    expect($setlist)->not->toBeNull();

    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.show', ['banda' => $banda, 'setlist' => $setlist])
        ->set('selectedCancionId', $cancion->id)
        ->set('cancionNota', 'Tono D, intro guitarra')
        ->call('addCancion')
        ->assertHasNoErrors();

    expect($setlist->fresh()->canciones->pluck('id'))->toContain($cancion->id);
    expect($setlist->fresh()->canciones->first()->pivot->nota)->toBe('Tono D, intro guitarra');
});
