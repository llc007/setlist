<?php

namespace App\Livewire;

use App\Models\Cancion;
use App\Models\CancionRecurso;
use Livewire\Attributes\On;
use Livewire\Component;

class GestionarRecursos extends Component
{
    public $cancionId;
    public $tituloCancion;

    // Campos del formulario
    public $tipo = 'youtube';
    public $url = '';
    public $etiqueta = '';

    #[On('abrir-modal-recursos')]
    public function cargarCancion($id)
    {
        $cancion = Cancion::findOrFail($id);
        $this->cancionId = $cancion->id;
        $this->tituloCancion = $cancion->titulo;

        // Abrimos el modal usando JS desde el componente
        $this->dispatch('modal-show', name: 'modal-recursos');
    }

    public function guardar()
    {
        $this->validate([
            'tipo' => 'required',
            'url' => 'required|url',
            'etiqueta' => 'required|min:3',
        ]);

        CancionRecurso::create([
            'cancion_id' => $this->cancionId,
            'tipo' => $this->tipo,
            'url' => $this->url,
            'etiqueta' => $this->etiqueta,
        ]);

        $this->reset(['url', 'etiqueta', 'tipo']);
        $this->dispatch('modal-close', name: 'modal-recursos');
        $this->dispatch('cancion-actualizada'); // Para refrescar la lista
    }

    public function render()
    {
        return view('livewire.gestionar-recursos');
    }
}
