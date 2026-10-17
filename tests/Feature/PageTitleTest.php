<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Setlist;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('has Que Cantamos configured as the application name', function () {
    expect(config('app.name'))->toBe('Que Cantamos');
});

it('displays Que Cantamos in the welcome page title', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee('<title>Que Cantamos — El setlist perfecto para tu banda en vivo</title>', false);
});

it('displays Que Cantamos in the login page title', function () {
    get(route('login'))
        ->assertOk()
        ->assertSee('<title>Que Cantamos</title>', false);
});

it('displays Que Cantamos in repertorio index page title', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('repertorio'))
        ->assertOk()
        ->assertSee('<title>Repertorio · Que Cantamos</title>', false);
});

it('displays song title and Que Cantamos in song view page title', function () {
    $user = User::factory()->create();
    $categoria = Categoria::factory()->create();
    $cancion = Cancion::factory()->create([
        'categoria_id' => $categoria->id,
        'titulo' => 'Sé que mi Redentor vive',
        'letra' => 'Letra de prueba',
        'pdf_path' => null,
    ]);

    actingAs($user)
        ->get(route('ver-cancion', $cancion))
        ->assertOk()
        ->assertSee('<title>Sé que mi Redentor vive · Que Cantamos</title>', false);
});

it('displays setlist title and Que Cantamos in banda setlist show page title', function () {
    $banda = Banda::create(['nombre' => 'Banda de Prueba', 'slug' => 'banda-de-prueba']);
    $user = User::factory()->create();
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Culto Dominical',
        'fecha' => now()->toDateString(),
        'tipo' => 'culto',
    ]);

    actingAs($user)
        ->get(route('bandas.setlists.show', ['banda' => $banda->slug, 'setlist' => $setlist->id]))
        ->assertOk()
        ->assertSee('<title>Culto Dominical - Banda de Prueba · Que Cantamos</title>', false);
});
