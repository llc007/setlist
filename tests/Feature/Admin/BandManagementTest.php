<?php

use App\Models\Banda;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superAdminRole = Role::findOrCreate('SuperAdministrador');
    $this->user = User::factory()->create();
    $this->user->assignRole($superAdminRole);
});

it('permite a un superadministrador acceder al mantenedor global de bandas', function () {
    $this->actingAs($this->user)
        ->get(route('admin.bandas.index'))
        ->assertSuccessful()
        ->assertSeeLivewire('pages::admin.bandas.index');
});

it('permite a un superadministrador crear y actualizar una banda y su ámbito', function () {
    Livewire::actingAs($this->user)
        ->test('pages::admin.bandas.index')
        ->set('nombre', 'Banda Coral')
        ->set('tipo_ambito', 'cristiano')
        ->call('saveBanda')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('bandas', [
        'nombre' => 'Banda Coral',
        'tipo_ambito' => 'cristiano',
    ]);
});

it('permite a un superadministrador editar una banda existente', function () {
    $banda = Banda::create([
        'nombre' => 'Banda Eventos VIP',
        'slug' => 'banda-eventos-vip',
        'tipo_ambito' => 'secular',
    ]);

    Livewire::actingAs($this->user)
        ->test('pages::admin.bandas.index')
        ->call('openEditModal', $banda->id)
        ->set('nombre', 'Banda Eventos Gold')
        ->set('tipo_ambito', 'mixto')
        ->call('saveBanda')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('bandas', [
        'id' => $banda->id,
        'nombre' => 'Banda Eventos Gold',
        'tipo_ambito' => 'mixto',
    ]);
});
