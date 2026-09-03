<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('asigna el ámbito por defecto en la creación de bandas y filtra el repertorio según el ámbito de la banda activa', function () {
    $user = User::factory()->create();

    // Crear banda cristiana
    $bandaCristiana = Banda::create([
        'nombre' => 'Ministerio de Alabanza',
        'slug' => 'ministerio-alabanza',
        'tipo_ambito' => 'cristiano',
    ]);
    $bandaCristiana->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    // Canciones en repertorio público
    Cancion::create(['titulo' => 'Cuán Grande es Él', 'ambito' => 'cristiano', 'es_publica' => true]);
    Cancion::create(['titulo' => 'De Música Ligera', 'ambito' => 'secular', 'es_publica' => true]);

    // Establecer la banda activa en sesión
    session(['active_banda_id' => $bandaCristiana->id]);

    Livewire::actingAs($user)
        ->test('pages::repertorio.index')
        ->assertSet('ambito', 'cristiano')
        ->assertSee('Cuán Grande es Él')
        ->assertDontSee('De Música Ligera');
});

it('permite filtrar categorías por ámbito en el mantenedor de administración', function () {
    Role::findOrCreate('SuperAdministrador');
    $user = User::factory()->create();
    $user->assignRole('SuperAdministrador');

    $cat1 = Categoria::create(['nombre' => 'Adoración Intima', 'slug' => 'adoracion-intima', 'ambito' => 'cristiano']);
    $cat2 = Categoria::create(['nombre' => 'Rock Pop 80s', 'slug' => 'rock-pop-80s', 'ambito' => 'secular']);

    Livewire::actingAs($user)
        ->test('pages::admin.categorias.index')
        ->set('ambitoFilter', 'cristiano')
        ->assertSee('Adoración Intima')
        ->assertViewHas('categorias', function ($categorias) use ($cat1, $cat2) {
            return $categorias->contains($cat1) && ! $categorias->contains($cat2);
        });
});
