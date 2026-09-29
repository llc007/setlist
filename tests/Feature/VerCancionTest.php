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

it('can enter and exit interactive edit mode and save chords directly', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Chords Drag Song',
        'letra' => "        A        E\nLetra de prueba",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->assertSet('modoEdicion', false)
        ->assertSee('Editar Acordes y Letra')
        ->set('modoEdicion', true)
        ->assertSet('modoEdicion', true)
        ->call('guardarLetra', "        D        A\nLetra editada")
        ->assertSet('modoEdicion', false)
        ->assertDispatched('cancion-actualizada');

    \Pest\Laravel\assertDatabaseHas('canciones', [
        'id' => $cancion->id,
        'letra' => "        D        A\nLetra editada",
    ]);
});

it('refreshes show page data when cancion-actualizada is dispatched', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Old Title',
        'tono_original' => 'A',
        'letra' => 'Old lyrics',
        'pdf_path' => null,
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->assertSet('tonoActual', 'A')
        ->assertSee('Old Title');

    // Update in DB like another component would
    $cancion->update([
        'titulo' => 'New Updated Title',
        'tono_original' => 'G',
        'letra' => 'New updated lyrics',
    ]);

    // Dispatch event
    $component->dispatch('cancion-actualizada')
        ->assertSet('tonoActual', 'G')
        ->assertSet('letra', 'New updated lyrics')
        ->assertSee('New Updated Title');
});

it('can transpose and permanently save transposed key and chords', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Transposition Test Song',
        'tono_original' => 'A',
        'letra' => "[A]Sé que mi [D]Redentor vive\n[E]Y al fin se levantará",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->assertSet('transposicion', 0)
        ->assertSet('tonoActual', 'A')
        ->call('cambiarTono', -2)
        ->assertSet('transposicion', -2)
        ->assertSet('tonoActual', 'G')
        ->assertSee('Fijar en G')
        ->call('guardarTonoTranspuesto')
        ->assertSet('transposicion', 0)
        ->assertSet('tonoActual', 'G')
        ->assertDispatched('cancion-actualizada');

    \Pest\Laravel\assertDatabaseHas('canciones', [
        'id' => $cancion->id,
        'tono_original' => 'G',
        'letra' => "[G]Sé que mi [C]Redentor vive\n[D]Y al fin se levantará",
    ]);
});

it('can reset transposed key back to original', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Reset Test Song',
        'tono_original' => 'C',
        'letra' => 'Letra',
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->assertSet('transposicion', 0)
        ->call('cambiarTono', 2)
        ->assertSet('transposicion', 2)
        ->assertSet('tonoActual', 'D')
        ->call('restablecerTono')
        ->assertSet('transposicion', 0)
        ->assertSet('tonoActual', 'C');
});

it('transposes song lyrics when key is changed in edit modal', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Modal Transpose Song',
        'tono_original' => 'A',
        'letra' => "        A        D\nLetra con acordes",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('canciones.editar')
        ->call('cargarCancion', $cancion->id)
        ->assertSet('tono_original', 'A')
        ->assertSet('transponer_acordes', true)
        ->set('tono_original', 'G')
        ->call('guardar')
        ->assertDispatched('modal-close')
        ->assertDispatched('cancion-actualizada');

    \Pest\Laravel\assertDatabaseHas('canciones', [
        'id' => $cancion->id,
        'tono_original' => 'G',
        'letra' => "        G        C\nLetra con acordes",
    ]);
});

it('does not transpose lyrics when transponer_acordes is disabled in edit modal', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Modal No Transpose Song',
        'tono_original' => 'A',
        'letra' => "        A        D\nLetra intacta",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('canciones.editar')
        ->call('cargarCancion', $cancion->id)
        ->set('transponer_acordes', false)
        ->set('tono_original', 'G')
        ->call('guardar');

    \Pest\Laravel\assertDatabaseHas('canciones', [
        'id' => $cancion->id,
        'tono_original' => 'G',
        'letra' => "        A        D\nLetra intacta",
    ]);
});
