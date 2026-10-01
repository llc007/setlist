<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
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
        ->set('cancionProposito', 'Especial ofrenda')
        ->set('cancionTono', 'D')
        ->set('cancionObservacion', 'Intro con piano suave')
        ->call('addCancion')
        ->assertHasNoErrors();

    expect($setlist->fresh()->canciones->pluck('id'))->toContain($cancion->id);
    $pivot = $setlist->fresh()->canciones->first()->pivot;
    expect($pivot->proposito)->toBe('Especial ofrenda');
    expect($pivot->tono)->toBe('D');
    expect($pivot->observacion)->toBe('Intro con piano suave');

    // Test editar detalles
    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.show', ['banda' => $banda, 'setlist' => $setlist])
        ->call('openEditSongModal', $pivot->id)
        ->assertSet('editingProposito', 'Especial ofrenda')
        ->assertSet('editingTono', 'D')
        ->set('editingProposito', 'Adoración')
        ->set('editingTono', 'E')
        ->set('editingObservacion', 'Modular al final')
        ->call('updateCancionDetalles')
        ->assertHasNoErrors();

    $updatedPivot = $setlist->fresh()->canciones->first()->pivot;
    expect($updatedPivot->proposito)->toBe('Adoración');
    expect($updatedPivot->tono)->toBe('E');
    expect($updatedPivot->observacion)->toBe('Modular al final');
});

it('permite filtrar canciones por categoría y buscar por título al agregar al setlist', function () {
    $user = User::factory()->create();
    $banda = Banda::create(['nombre' => 'Banda Central', 'slug' => 'banda-central']);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $catHimnos = Categoria::create(['nombre' => 'Himnario', 'slug' => 'himnario']);
    $catCoritos = Categoria::create(['nombre' => 'Coritos', 'slug' => 'coritos']);

    $himno = Cancion::create(['titulo' => 'Grande es Tu Fidelidad', 'categoria_id' => $catHimnos->id, 'banda_id' => $banda->id, 'es_publica' => true]);
    $corito = Cancion::create(['titulo' => 'Alabaré', 'categoria_id' => $catCoritos->id, 'banda_id' => $banda->id, 'es_publica' => true]);

    $banda->repertorio()->attach([$himno->id, $corito->id]);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Servicio Especial',
        'fecha' => now()->format('Y-m-d'),
        'tipo' => 'culto',
    ]);

    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.show', ['banda' => $banda, 'setlist' => $setlist])
        ->set('filtroCategoriaId', $catHimnos->id)
        ->assertViewHas('bandaCanciones', function ($canciones) use ($himno, $corito) {
            return $canciones->contains($himno) && ! $canciones->contains($corito);
        })
        ->set('filtroCategoriaId', null)
        ->set('searchCancion', 'Alabaré')
        ->assertViewHas('bandaCanciones', function ($canciones) use ($himno, $corito) {
            return $canciones->contains($corito) && ! $canciones->contains($himno);
        });
});

it('genera el formato llamativo para compartir el setlist en whatsapp', function () {
    $user = User::factory()->create();
    $banda = Banda::create(['nombre' => 'Banda Ebenezer', 'slug' => 'banda-ebenezer']);
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $cancion = Cancion::create([
        'titulo' => 'Rendido a Tus Pies',
        'artista' => 'Miel San Marcos',
        'tono_original' => 'G',
        'es_publica' => true,
    ]);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Vigilia de Oración',
        'fecha' => '2026-10-15',
        'tipo' => 'culto',
        'descripcion' => 'Traer instrumentos afinados',
    ]);

    $setlist->canciones()->attach($cancion->id, [
        'orden' => 1,
        'proposito' => 'Adoración',
        'tono' => 'A',
        'observacion' => 'Subir medio tono al final',
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::bandas.setlists.show', ['banda' => $banda, 'setlist' => $setlist])
        ->call('openWhatsappModal')
        ->assertSet('showWhatsappModal', true);

    $whatsappText = $component->instance()->getWhatsappText();

    expect($whatsappText)
        ->toContain('SETLIST: VIGILIA DE ORACIÓN')
        ->toContain('Banda Ebenezer')
        ->toContain('15/10/2026')
        ->toContain('1️⃣ *Rendido a Tus Pies*')
        ->toContain('*Propósito:* Adoración')
        ->toContain('*Tono:* A')
        ->toContain('*Obs:* Subir medio tono al final')
        ->toContain(route('setlists.public', $setlist->id));
});

it('permite a cualquier persona ver el setlist compartido sin iniciar sesión', function () {
    $banda = Banda::create(['nombre' => 'Banda Sinaí', 'slug' => 'banda-sinai']);
    $user = User::factory()->create();

    $cancion = Cancion::create([
        'titulo' => 'Tu Fidelidad',
        'artista' => 'Marcos Witt',
        'tono_original' => 'C',
        'es_publica' => true,
    ]);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Culto de Adoración',
        'fecha' => '2026-11-20',
        'tipo' => 'culto',
    ]);

    $setlist->canciones()->attach($cancion->id, [
        'orden' => 1,
        'proposito' => 'Inicio',
        'tono' => 'D',
    ]);

    // Petición como invitado (sin auth)
    $this->get(route('setlists.public', $setlist->id))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::setlists.public-show')
        ->assertSee('Culto de Adoración')
        ->assertSee('Banda Sinaí')
        ->assertSee('Tu Fidelidad')
        ->assertSee('Inicio')
        ->assertSee('Tono: D')
        ->assertSee('<title>quetocamos.cl</title>', false);
});

it('permite reordenar las canciones del setlist mediante drag and drop', function () {
    $banda = Banda::create(['nombre' => 'Banda Getsemaní', 'slug' => 'banda-getsemani']);
    $user = User::factory()->create();
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $c1 = Cancion::create(['titulo' => 'Canción Uno', 'banda_id' => $banda->id, 'es_publica' => true]);
    $c2 = Cancion::create(['titulo' => 'Canción Dos', 'banda_id' => $banda->id, 'es_publica' => true]);
    $c3 = Cancion::create(['titulo' => 'Canción Tres', 'banda_id' => $banda->id, 'es_publica' => true]);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Setlist Reordenar',
        'fecha' => '2026-12-01',
        'tipo' => 'ensayo',
    ]);

    $setlist->canciones()->attach($c1->id, ['orden' => 1]);
    $setlist->canciones()->attach($c2->id, ['orden' => 2]);
    $setlist->canciones()->attach($c3->id, ['orden' => 3]);

    $pivotIds = $setlist->canciones()->orderBy('cancion_setlist.orden')->get()->pluck('pivot.id')->toArray();
    expect($pivotIds)->toHaveCount(3);

    // Invertir el orden: [c3, c2, c1]
    $newOrder = [$pivotIds[2], $pivotIds[1], $pivotIds[0]];

    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.show', ['banda' => $banda, 'setlist' => $setlist])
        ->call('reorderCanciones', $newOrder)
        ->assertHasNoErrors();

    $reorderedCanciones = $setlist->fresh()->canciones()->orderBy('cancion_setlist.orden')->get();
    expect($reorderedCanciones[0]->id)->toBe($c3->id)
        ->and($reorderedCanciones[1]->id)->toBe($c2->id)
        ->and($reorderedCanciones[2]->id)->toBe($c1->id);
});

it('muestra la lista rápida de canciones en la grilla de setlists', function () {
    $banda = Banda::create(['nombre' => 'Banda Central', 'slug' => 'banda-central-2']);
    $user = User::factory()->create();
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $cancion1 = Cancion::create(['titulo' => 'Digno es el Señor', 'banda_id' => $banda->id, 'tono_original' => 'A', 'es_publica' => true]);
    $cancion2 = Cancion::create(['titulo' => 'Cuan Grande es Dios', 'banda_id' => $banda->id, 'tono_original' => 'C', 'es_publica' => true]);

    $setlist = Setlist::create([
        'banda_id' => $banda->id,
        'user_id' => $user->id,
        'nombre' => 'Culto Pinterest',
        'fecha' => '2026-10-10',
        'tipo' => 'culto',
    ]);

    $setlist->canciones()->attach($cancion1->id, ['orden' => 1, 'proposito' => 'Inicio', 'tono' => 'A']);
    $setlist->canciones()->attach($cancion2->id, ['orden' => 2, 'proposito' => 'Adoración', 'tono' => 'D']);

    Livewire::actingAs($user)
        ->test('pages::bandas.setlists.index', ['banda' => $banda])
        ->assertSee('Culto Pinterest')
        ->assertSee('Digno es el Señor')
        ->assertSee('Cuan Grande es Dios')
        ->assertSee('Inicio')
        ->assertSee('Adoración')
        ->assertSee('A')
        ->assertSee('D');
});

it('redirecciona la ruta /setlists a los setlists de la banda activa', function () {
    $banda = Banda::create(['nombre' => 'Banda Primaria', 'slug' => 'banda-primaria']);
    $user = User::factory()->create();
    $banda->miembros()->attach($user->id, ['rol' => 'Administrador de Banda']);

    $this->actingAs($user)
        ->withSession(['active_banda_id' => $banda->id])
        ->get('/setlists')
        ->assertRedirect(route('bandas.setlists.index', $banda->slug));
});
