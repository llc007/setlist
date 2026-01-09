<?php

namespace App\Livewire;

use App\Models\Cancion;
use Livewire\Component;
use Livewire\WithPagination;

class ListarCanciones extends Component
{
    use WithPagination;

    public $buscar = '';

    // Esto limpia la paginación cuando escribes en el buscador
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function render()
    {
        $canciones = Cancion::query()
            ->with(['categoria', 'recursos'])
            ->when($this->buscar, function ($query) {
                $query->where('titulo', 'like', '%'.$this->buscar.'%')
                    ->orWhere('artista', 'like', '%'.$this->buscar.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.listar-canciones', [
            'canciones' => $canciones,
        ]);
    }
}
