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
    public $mostrandoFormulario = false; // Estado del formulario

    #[On('abrir-modal-recursos')]
    public function cargarCancion($id)
    {
        $cancion = Cancion::findOrFail($id);
        $this->cancionId = $cancion->id;
        $this->tituloCancion = $cancion->titulo;
        $this->mostrandoFormulario = false; // Empezamos siempre con el formulario cerrado

        // Abrimos el modal usando JS desde el componente
        $this->dispatch('modal-show', name: 'modal-recursos');
    }

    public function toggleFormulario()
    {
        $this->mostrandoFormulario = !$this->mostrandoFormulario;
    }

    public function guardar()
    {
        // Si la URL y la Etiqueta están vacías, asumimos que el usuario 
        // solo quiere cerrar el modal después de haber borrado algo o solo mirar.
        if (empty($this->url) && empty($this->etiqueta)) {
            $this->reset(['url', 'etiqueta', 'tipo']);

            $this->dispatch('modal-close', name: 'modal-recursos');

            return; // Salimos de la función sin ejecutar la validación
        }

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

    public function eliminarRecurso($id)
    {
        CancionRecurso::destroy($id);
        // No cerramos el modal, solo refrescamos la lista de la canción
        $this->dispatch('cancion-actualizada');
    }

    public function render()
    {
        return view('livewire.gestionar-recursos', [
            'recursosExistentes' => $this->cancionId
                ? CancionRecurso::where('cancion_id', $this->cancionId)->get()
                : []
        ]);
    }
}
