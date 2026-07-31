<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $cancionId;
    public $titulo;
    public $categoria_id;
    public $tono_original;
    public $codigo;

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
        $this->dispatch('cancion-actualizada');
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
    <flux:modal name="editar-cancion" class="md:w-[500px]">
        <form wire:submit="guardar" class="space-y-4">
            <div>
                <flux:heading size="lg">Editar Canción</flux:heading>
                <flux:subheading>Modifica los detalles principales de la canción.</flux:subheading>
            </div>

            <flux:input wire:model="titulo" label="Título" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="categoria_id" label="Categoría" required>
                    @foreach ($categorias as $categoria)
                        <flux:select.option value="{{ $categoria->id }}">{{ $categoria->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="tono_original" label="Tono" />
            </div>

            <flux:input wire:model="codigo" label="Código (Ej: H005)" />

            <div class="flex gap-2 justify-end mt-6">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
