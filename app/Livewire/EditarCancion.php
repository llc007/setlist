<?php

namespace App\Livewire;

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\Attributes\On;

class EditarCancion extends Component
{
    public $cancionId, $titulo, $artista, $categoria_id, $tono_original, $letra;

    #[On('abrir-modal-edicion')]
    public function cargarCancion($id)
    {
        $cancion = Cancion::findOrFail($id);
        $this->cancionId = $cancion->id;
        $this->titulo = $cancion->titulo;
        $this->artista = $cancion->artista;
        $this->categoria_id = $cancion->categoria_id;
        $this->tono_original = $cancion->tono_original;
        $this->letra = $cancion->letra;

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
            'artista' => $this->artista,
            'categoria_id' => $this->categoria_id,
            'tono_original' => $this->tono_original,
            'letra' => $this->letra,
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
