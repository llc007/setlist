<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component {
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

        $categoria = Categoria::find($this->categoria_id);

        if ($categoria && strtolower($categoria->nombre) === 'himnario' && $this->numero) {
            $numeroFormateado = str_pad($this->numero, 3, '0', STR_PAD_LEFT);
            $codigoFinal = 'H'.$numeroFormateado;
        }

        Cancion::create([
            'titulo' => $this->titulo,
            'tono_original' => $this->tono_original,
            'categoria_id' => $this->categoria_id,
            'codigo' => $codigoFinal,
        ]);

        $this->reset();

        $this->dispatch('modal-close', name: 'nueva-cancion');
        $this->dispatch('cancion-creada');
    }

    public function with()
    {
        return [
            'categorias' => Categoria::orderBy('nombre')->get(),
        ];
    }
};
?>

<div>
    <form wire:submit="guardar" class="space-y-4">
        <flux:input wire:model="titulo" label="Título de la Canción" placeholder="Ej: Que Se Abra El Cielo" required />

        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model.live="categoria_id" label="Categoría" placeholder="Selecciona una opción" required>
                @foreach ($categorias as $categoria)
                    <flux:select.option value="{{ $categoria->id }}">{{ $categoria->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tono_original" label="Tono (Opcional)" placeholder="Ej: G, C, F#m..." />
        </div>

        @php
            $categoriaSeleccionada = $categorias->firstWhere('id', $categoria_id);
        @endphp

        @if ($categoriaSeleccionada && strtolower($categoriaSeleccionada->nombre) === 'himnario')
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <flux:input wire:model="numero" type="number" label="Número de Himno" placeholder="Ej: 5"
                    hint="Generará automáticamente el código (Ej: H005)" />
            </div>
        @endif

        <div class="flex gap-2 justify-end mt-6">
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Guardar Canción</flux:button>
        </div>
    </form>
</div>
