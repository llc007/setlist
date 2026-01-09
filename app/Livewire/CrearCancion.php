<?php

namespace App\Livewire;

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CrearCancion extends Component
{
    #[Rule('required|min:3', as: 'titulo')]
    public $titulo = '';

    #[Rule('nullable|min:3', as: 'artista')]
    public $artista = '';

    #[Rule('nullable', as: 'tono_original')]
    public $tono_original = '';

    #[Rule('required', as: 'categoria_id')]
    public $categoria_id = '';

    #[Rule('nullable', as: 'letra')]
    public $letra = '';

    public function guardar()
    {
        $this->validate();

        Cancion::create([
            'titulo' => $this->titulo,
            'artista' => $this->artista,
            'tono_original' => $this->tono_original,
            'categoria_id' => $this->categoria_id,
            'letra' => $this->letra,
        ]);
        // limpiar los campos
        $this->reset();

        // Cerrar el modal
        $this->dispatch('modal-close', name: 'nueva-cancion');

        // 2. DISPARAMOS EL EVENTO (Fíjate en el nombre exacto)
        $this->dispatch('cancion-creada');

        // $this->dispatch('notificacion', message: 'Canción creada correctamente', type: 'success');
    }

    public function render()
    {
        return view('livewire.crear-cancion', ['categorias' => Categoria::orderBy('nombre')->get()]);
    }
}
