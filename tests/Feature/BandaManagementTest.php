<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
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

it('muestra 30 canciones por defecto en el repertorio de la banda', function () {
    $user = User::factory()->create();
    $banda = Banda::create([
        'nombre' => 'Banda Repertorio',
        'slug' => 'banda-repertorio',
    ]);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $cancionesIds = [];
    for ($i = 1; $i <= 35; $i++) {
        $cancion = Cancion::create([
            'titulo' => "Canción Número {$i}",
            'es_publica' => true,
        ]);
        $cancionesIds[] = $cancion->id;
    }

    $banda->repertorio()->attach($cancionesIds);

    Livewire::actingAs($user)
        ->test('pages::bandas.repertorio.index', ['banda' => $banda])
        ->assertViewHas('canciones', function ($canciones) {
            return $canciones->perPage() === 30 && $canciones->count() === 30;
        });
});

it('permite filtrar canciones en el repertorio de la banda presionando los badges de categorías', function () {
    $user = User::factory()->create();
    $banda = Banda::create([
        'nombre' => 'Banda Categorías',
        'slug' => 'banda-categorias',
    ]);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $catAdoracion = Categoria::create(['nombre' => 'Adoración', 'slug' => 'adoracion']);
    $catAlabanza = Categoria::create(['nombre' => 'Alabanza', 'slug' => 'alabanza']);

    $cancion1 = Cancion::create([
        'titulo' => 'Canción de Adoración',
        'categoria_id' => $catAdoracion->id,
        'es_publica' => true,
    ]);
    $cancion2 = Cancion::create([
        'titulo' => 'Canción de Alabanza',
        'categoria_id' => $catAlabanza->id,
        'es_publica' => true,
    ]);

    $banda->repertorio()->attach([$cancion1->id, $cancion2->id]);

    Livewire::actingAs($user)
        ->test('pages::bandas.repertorio.index', ['banda' => $banda])
        ->assertSee('Adoración')
        ->assertSee('Alabanza')
        ->assertSee('Canción de Adoración')
        ->assertSee('Canción de Alabanza')
        ->call('filterByCategoria', $catAdoracion->id)
        ->assertSet('categoriaId', (string) $catAdoracion->id)
        ->assertSee('Canción de Adoración')
        ->assertDontSee('Canción de Alabanza')
        ->call('filterByCategoria', $catAdoracion->id)
        ->assertSet('categoriaId', '')
        ->assertSee('Canción de Adoración')
        ->assertSee('Canción de Alabanza');
});

it('permite editar rápidamente el título, artista y categoría de una canción desde el repertorio de la banda', function () {
    $user = User::factory()->create();
    $banda = Banda::create([
        'nombre' => 'Banda Edición Rápida',
        'slug' => 'banda-edicion-rapida',
    ]);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $catInicial = Categoria::create(['nombre' => 'Adoración', 'slug' => 'adoracion']);
    $catNueva = Categoria::create(['nombre' => 'Alabanza', 'slug' => 'alabanza']);

    $cancion = Cancion::create([
        'titulo' => 'Título Original',
        'artista' => 'Artista Original',
        'categoria_id' => $catInicial->id,
        'es_publica' => true,
    ]);

    $banda->repertorio()->attach($cancion->id);

    Livewire::actingAs($user)
        ->test('pages::bandas.repertorio.index', ['banda' => $banda])
        ->call('openEditModal', $cancion->id)
        ->assertSet('showEditModal', true)
        ->assertSet('editingCancionId', $cancion->id)
        ->assertSet('editTitulo', 'Título Original')
        ->assertSet('editArtista', 'Artista Original')
        ->assertSet('editCategoriaId', (string) $catInicial->id)
        ->set('editTitulo', 'Título Actualizado')
        ->set('editArtista', 'Artista Actualizado')
        ->set('editCategoriaId', (string) $catNueva->id)
        ->call('updateCancion')
        ->assertHasNoErrors()
        ->assertSet('showEditModal', false)
        ->assertSee('Título Actualizado')
        ->assertSee('Artista Actualizado');

    $cancion->refresh();
    expect($cancion->titulo)->toBe('Título Actualizado')
        ->and($cancion->artista)->toBe('Artista Actualizado')
        ->and($cancion->categoria_id)->toBe($catNueva->id);
});
