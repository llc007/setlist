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


    #[Rule('nullable', as: 'tono_original')]
    public $tono_original = '';

    #[Rule('required', as: 'categoria_id')]
    public $categoria_id = '';

    #[Rule('nullable', as: 'letra')]
    public $letra = '';

    #[Rule('nullable', as: 'numero')]
    public $numero = '';



    public function guardar()
    {
        $this->validate();

        $codigoFinal = null;

        // 2. Lógica para armar el código si es Himnario
        // Primero buscamos el nombre de la categoría seleccionada
        $categoria = Categoria::find($this->categoria_id);

        if ($categoria && strtolower($categoria->nombre) === 'himnario' && $this->numero) {
            /**
             * str_pad toma el número (ej: 5) y lo rellena con "0" 
             * hasta tener 3 caracteres, resultando en "005".
             */
            $numeroFormateado = str_pad($this->numero, 3, '0', STR_PAD_LEFT);
            $codigoFinal = 'H' . $numeroFormateado;
        }

        Cancion::create([
            'titulo' => $this->titulo,
            'tono_original' => $this->tono_original,
            'categoria_id' => $this->categoria_id,
            'codigo' => $codigoFinal,
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
