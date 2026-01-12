<?php

namespace App\Livewire;

use App\Models\Cancion;
use App\Models\Categoria;
// Importante
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ListarCanciones extends Component
{
    use WithPagination;

    public $buscar = '';

    public $categoria_id = '';

    // Esto limpia la paginación cuando escribes en el buscador
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingCategoriaId()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        // Esto resetea las propiedades a su valor inicial
        $this->reset(['buscar', 'categoria_id']);

        // También es buena idea resetear la paginación por si acaso
        $this->resetPage();
    }

    // Este atributo hace que el componente se refresque solo
    #[On('cancion-actualizada')]
    public function render()
    {
        $canciones = Cancion::query()
            ->with(['categoria', 'recursos'])

            // 1. Filtro por Categoría (si hay una elegida)
            ->when($this->categoria_id, function ($query) {
                $query->where('categoria_id', $this->categoria_id);
            })

            // 2. Filtro por Texto (dentro de los resultados anteriores)
            ->when($this->buscar, function ($query) {
                $query->where(function ($q) { // Agrupamos para que el OR no anule la categoría
                    $q->where('titulo', 'like', '%' . $this->buscar . '%')
                        ->orWhere('artista', 'like', '%' . $this->buscar . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.listar-canciones', [
            'canciones' => $canciones,
            'categorias' => Categoria::orderBy('nombre')->get(), // Pasamos las categorías al select
        ]);
    }
}
