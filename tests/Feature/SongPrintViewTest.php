<?php

use App\Models\Cancion;
use App\Models\User;

it('permite abrir la vista completa e imprimible de una canción en una nueva pestaña', function () {
    $user = User::factory()->create();
    $cancion = Cancion::create([
        'titulo' => 'Alaba a Dios',
        'artista' => 'Danny Berrios',
        'letra' => "[Intro] G C G C\n[Estrofa 1]\nG\nDios no te ha olvidado",
        'tono_original' => 'G',
        'es_publica' => true,
    ]);

    $this->actingAs($user)
        ->get(route('canciones.imprimir', ['cancion' => $cancion->id, 'semitonos' => 2, 'tamanio' => 18]))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::repertorio.imprimir')
        ->assertSee('Alaba a Dios')
        ->assertSee('Danny Berrios');
});
