<?php

use App\Models\Banda;
use App\Models\User;
use Livewire\Livewire;

it('permite al administrador de la banda acceder y cambiar la configuración y el ámbito', function () {
    $adminUser = User::factory()->create();
    $integranteUser = User::factory()->create();

    $banda = Banda::create([
        'nombre' => 'Banda Oración',
        'slug' => 'banda-oracion',
        'tipo_ambito' => 'cristiano',
    ]);

    $banda->miembros()->attach($adminUser->id, ['rol' => 'Administrador de Banda']);
    $banda->miembros()->attach($integranteUser->id, ['rol' => 'Integrante de Banda']);

    // Administrador accede con éxito
    $this->actingAs($adminUser)
        ->get(route('bandas.configuracion', $banda->slug))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::bandas.configuracion');

    // Integrante normal no puede acceder
    $this->actingAs($integranteUser)
        ->get(route('bandas.configuracion', $banda->slug))
        ->assertForbidden();

    // Actualización por parte del admin
    Livewire::actingAs($adminUser)
        ->test('pages::bandas.configuracion', ['banda' => $banda])
        ->set('nombre', 'Banda Oración y Gracia')
        ->set('tipo_ambito', 'mixto')
        ->call('saveSettings')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('bandas', [
        'id' => $banda->id,
        'nombre' => 'Banda Oración y Gracia',
        'tipo_ambito' => 'mixto',
    ]);
});
