<?php

use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superAdminRole = Role::findOrCreate('SuperAdministrador');
    $this->user = User::factory()->create();
    $this->user->assignRole($superAdminRole);
});

it('permite a un superadministrador acceder a la gestión de categorías', function () {
    $this->actingAs($this->user)
        ->get(route('admin.categorias.index'))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::admin.categorias.index');
});

it('permite buscar categorías por nombre o slug', function () {
    Categoria::query()->delete();

    $cat1 = Categoria::create(['nombre' => 'Tema Especial Adoración', 'slug' => 'tema-especial-adoracion']);
    $cat2 = Categoria::create(['nombre' => 'Ritmo Rápido Festejo', 'slug' => 'ritmo-rapido-festejo']);

    Livewire::actingAs($this->user)
        ->test('pages::admin.categorias.index')
        ->set('search', 'Adoración')
        ->assertSee('Tema Especial Adoración')
        ->assertViewHas('categorias', function ($categorias) use ($cat1, $cat2) {
            return $categorias->contains($cat1) && ! $categorias->contains($cat2);
        });
});

it('permite crear una nueva categoría con auto-slug', function () {
    Livewire::actingAs($this->user)
        ->test('pages::admin.categorias.index')
        ->set('nombre', 'Jóvenes')
        ->set('slug', 'jovenes')
        ->call('saveCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categorias', [
        'nombre' => 'Jóvenes',
        'slug' => 'jovenes',
    ]);
});

it('permite editar una categoría existente', function () {
    $cat = Categoria::create(['nombre' => 'Himnos Viejos', 'slug' => 'himnos-viejos']);

    Livewire::actingAs($this->user)
        ->test('pages::admin.categorias.index')
        ->call('openEditModal', $cat->id)
        ->set('nombre', 'Himnos Tradicionales')
        ->set('slug', 'himnos-tradicionales')
        ->call('saveCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categorias', [
        'id' => $cat->id,
        'nombre' => 'Himnos Tradicionales',
        'slug' => 'himnos-tradicionales',
    ]);
});

it('permite eliminar una categoría y desasociar sus canciones', function () {
    $cat = Categoria::create(['nombre' => 'Navidad', 'slug' => 'navidad']);
    $cancion = Cancion::create(['titulo' => 'Noche de Paz', 'categoria_id' => $cat->id]);

    Livewire::actingAs($this->user)
        ->test('pages::admin.categorias.index')
        ->call('confirmDelete', $cat->id)
        ->call('deleteCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('categorias', ['id' => $cat->id]);
    expect($cancion->fresh()->categoria_id)->toBeNull();
});
