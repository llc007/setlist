<?php

use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\User;
use App\Services\ChordTransposer;
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

it('can toggle between 1 and 2 columns for song lyrics and chords', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Column Test Song',
        'tono_original' => 'C',
        'letra' => "[C]Línea 1\n[G]Línea 2\n[Am]Línea 3\n[F]Línea 4",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->assertSet('tamanioLetra', 12)
        ->assertSet('columnas', 2)
        ->assertSee('12px')
        ->assertSee('2 Columnas')
        ->assertSee('md:grid-cols-2')
        ->assertSee('min-h-[750px]')
        ->assertSee('max-h-[1065px]')
        ->call('toggleColumnas')
        ->assertSet('columnas', 1)
        ->assertSee('1 Columna')
        ->call('toggleColumnas')
        ->assertSet('columnas', 2)
        ->assertSee('2 Columnas');
});

it('splits 2 columns starting column 2 with a section header in show view', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $letra = <<<'EOD'
[Estrofa 1]
[C]Alabaré a mi Dios
[G]Por su gran amor

[Coro]
[F]Santo es el Señor
[C]Digno de honor
EOD;

    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Section Split Song',
        'tono_original' => 'C',
        'letra' => $letra,
        'pdf_path' => null,
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::repertorio.show', ['cancion' => $cancion])
        ->set('columnas', 2);

    $html = $component->instance()->renderLetraConAcordes($cancion->letra);
    expect($html)->toContain('md:grid-cols-2');

    // Dividir las líneas directamente para verificar que columna 2 empieza con [Coro]
    [$col1, $col2] = ChordTransposer::dividirLineasEnDosColumnas(explode("\n", $cancion->letra));
    expect($col2[0])->toBe('[Coro]');
});

it('can toggle between 1 and 2 columns in print and fullscreen view', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Print Column Test Song',
        'tono_original' => 'C',
        'letra' => "[C]Línea 1\n[G]Línea 2\n[Am]Línea 3\n[F]Línea 4",
        'pdf_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.imprimir', ['cancion' => $cancion])
        ->assertSet('columnas', 1)
        ->assertSee('1 Columna')
        ->assertDontSee('print:grid-cols-2')
        ->call('toggleColumnas')
        ->assertSet('columnas', 2)
        ->assertSee('2 Columnas')
        ->assertSee('print:grid-cols-2')
        ->assertSee('max-w-7xl')
        ->call('toggleColumnas')
        ->assertSet('columnas', 1)
        ->assertSee('1 Columna');
});

it('loads 2 columns in print view when passed via url query parameter', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Print Query Param Song',
        'tono_original' => 'C',
        'letra' => "[C]Línea 1\n[G]Línea 2",
        'pdf_path' => null,
    ]);

    actingAs($user)
        ->get(route('canciones.imprimir', ['cancion' => $cancion, 'columnas' => 2]))
        ->assertOk()
        ->assertSee('print:grid-cols-2')
        ->assertSee('2 Columnas');
});
