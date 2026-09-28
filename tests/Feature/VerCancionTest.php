<?php

use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('displays song details and lyrics when no pdf is present', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Amazing Grace',
        'letra' => 'Amazing grace how sweet the sound',
        'pdf_path' => null,
    ]);

    actingAs($user)
        ->get(route('ver-cancion', $cancion))
        ->assertOk()
        ->assertSeeLivewire('pages::repertorio.show')
        ->assertSee('Amazing Grace')
        ->assertSee('Amazing grace how sweet the sound')
        ->assertDontSee('iframe');
});

it('displays pdf viewer when pdf is present', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'PDF Song',
        'pdf_path' => 'songs/score.pdf',
    ]);

    actingAs($user)
        ->get(route('ver-cancion', $cancion))
        ->assertOk()
        ->assertSeeLivewire('pages::repertorio.show')
        ->assertSee('PDF Song')
        ->assertSee('iframe')
        ->assertSee('songs/score.pdf');
});

it('displays pdf viewer from resources when pdf_path is null', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Resource PDF Song',
        'pdf_path' => null,
    ]);

    // Create a resource manually since we don't have a factory for it yet
    $cancion->recursos()->create([
        'tipo' => 'pdf',
        'url' => 'https://drive.google.com/file/d/12345/view',
        'etiqueta' => 'Drive PDF',
    ]);

    actingAs($user)
        ->get(route('ver-cancion', $cancion))
        ->assertOk()
        ->assertSeeLivewire('pages::repertorio.show')
        ->assertSee('iframe')
        ->assertSee('https://drive.google.com/file/d/12345/preview'); // Verify transformation
});

it('can update lyrics via interactive editor', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Lyrics Song',
        'letra' => 'Old Lyrics',
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->set('letra', 'New [C] Lyrics')
        ->call('guardarLetra')
        ->assertDispatched('modal-close');

    \Pest\Laravel\assertDatabaseHas('canciones', [
        'id' => $cancion->id,
        'letra' => 'New [C] Lyrics',
    ]);
});

it('displays edit chords and lyrics button and can open modal', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Song With Edit Button',
        'letra' => '[C] Line one',
        'pdf_path' => null,
    ]);

    actingAs($user)
        ->get(route('ver-cancion', $cancion))
        ->assertOk()
        ->assertSee('Editar Acordes y Letra');

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->call('abrirModalLetra')
        ->assertSet('letra', '[C] Line one')
        ->assertDispatched('modal-show', name: 'modal-interactivo');
});
