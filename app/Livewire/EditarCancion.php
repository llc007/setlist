<?php

namespace App\Livewire;

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\Attributes\On;

class EditarCancion extends Component
{
    public $cancionId, $titulo, $categoria_id, $tono_original, $codigo;

    #[On('abrir-modal-edicion')]
    public function cargarCancion($id)
    {
        $cancion = Cancion::findOrFail($id);
        $this->cancionId = $cancion->id;
        $this->titulo = $cancion->titulo;
        $this->categoria_id = $cancion->categoria_id;
        $this->tono_original = $cancion->tono_original;
        $this->codigo = $cancion->codigo;

        $this->dispatch('modal-show', name: 'editar-cancion');
    }

    public function guardar()
    {
        $this->validate([
            'titulo' => 'required|min:3',
            'categoria_id' => 'required',
        ]);

        $cancion = Cancion::findOrFail($this->cancionId);
        $cancion->update([
            'titulo' => $this->titulo,
            'categoria_id' => $this->categoria_id,
            'tono_original' => $this->tono_original,
            'codigo' => $this->codigo,
        ]);

        $this->dispatch('modal-close', name: 'editar-cancion');
        $this->dispatch('cancion-actualizada'); // Refresca la lista
    }

    public function render()
    {
        return view('livewire.editar-cancion', [
            'categorias' => Categoria::orderBy('nombre')->get()
        ]);
    }
}
