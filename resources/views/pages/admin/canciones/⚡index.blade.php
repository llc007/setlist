<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $buscar = '';
    public $categoria_id = '';
    public $cancionIdSiendoEliminada;
    public $tituloCancionSiendoEliminada;

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
        $this->reset(['buscar', 'categoria_id']);
        $this->resetPage();
    }

    #[On('cancion-actualizada')]
    #[On('cancion-creada')]
    public function render()
    {
        $canciones = Cancion::query()
            ->with(['categoria', 'recursos'])
            ->when($this->categoria_id, function ($query) {
                $query->where('categoria_id', $this->categoria_id);
            })
            ->when($this->buscar, function ($query) {
                $query->where(function ($q) {
                    $q->where('titulo', 'like', '%'.$this->buscar.'%')
                        ->orWhere('artista', 'like', '%'.$this->buscar.'%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('pages.admin.canciones.⚡index', [
            'canciones' => $canciones,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function confirmarEliminacion($id, $titulo)
    {
        $this->cancionIdSiendoEliminada = $id;
        $this->tituloCancionSiendoEliminada = $titulo;

        $this->dispatch('modal-show', name: 'confirmar-eliminacion');
    }

    public function eliminar()
    {
        $cancion = Cancion::findOrFail($this->cancionIdSiendoEliminada);
        $cancion->delete();

        $this->dispatch('modal-close', name: 'confirmar-eliminacion');
        $this->dispatch('cancion-creada');

        $this->reset(['cancionIdSiendoEliminada', 'tituloCancionSiendoEliminada']);
    }
};
?>

<div>
    <div>
        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            <flux:heading size="xl" class="flex-1">Biblioteca</flux:heading>

            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <flux:select wire:model.live="categoria_id" placeholder="Todas las categorías" class="sm:w-48">
                    <flux:select.option value="">Todas las categorías</flux:select.option>
                    @foreach ($categorias as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model.live.debounce.300ms="buscar" icon="magnifying-glass"
                    placeholder="Buscar canción..." class="flex-1 sm:w-64" />

                @if ($buscar || $categoria_id)
                    <flux:button variant="ghost" size="sm" wire:click="limpiarFiltros" icon="x-mark">
                        Limpiar
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Canción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Tono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Recursos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($canciones as $cancion)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $cancion->titulo }}</div>
                            <div class="text-sm text-zinc-500">{{ $cancion->artista }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge size="sm" inset="top bottom" color="zinc">
                                {{ $cancion->categoria->nombre ?? 'General' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-primary-500">
                            {{ $cancion->tono_original }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-3">
                                @foreach ($cancion->recursos as $recurso)
                                    <a href="{{ $recurso->url }}" target="_blank"
                                        class="text-zinc-400 hover:text-primary-500 transition-colors">
                                        @if ($recurso->tipo === 'pdf')
                                            <flux:icon.document-text variant="outline" class="size-5" />
                                        @elseif($recurso->tipo === 'youtube')
                                            <flux:icon.video-camera variant="outline" class="size-5" />
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-3">
                                <flux:button.group>
                                    <flux:button class="cursor-pointer" tooltip="Añadir Recurso" tooltip:position="bottom" variant="ghost" size="xs" icon="plus"
                                        wire:click="$dispatch('abrir-modal-recursos', { id: {{ $cancion->id }} })" />
                                    <flux:button 
                                        variant="ghost" 
                                        size="xs" 
                                        icon="pencil" 
                                        class="cursor-pointer"
                                        wire:click="$dispatch('abrir-modal-edicion', { id: {{ $cancion->id }} })" 
                                    />
                                    <flux:button 
                                        variant="ghost" 
                                        size="xs" 
                                        icon="trash" 
                                        class="cursor-pointer hover:text-red-500"
                                        wire:click="confirmarEliminacion({{ $cancion->id }}, '{{ addslashes($cancion->titulo) }}')" 
                                    />
                                </flux:button.group>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-zinc-500">
                            No se encontraron canciones {{ $buscar ? 'con "' . $buscar . '"' : '...' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $canciones->links() }}
    </div>

    <livewire:canciones.gestionar-recursos />
    <livewire:canciones.editar />

    <flux:modal name="confirmar-eliminacion" class="md:w-[400px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">¿Eliminar canción?</flux:heading>
                <flux:subheading>
                    Estás a punto de eliminar **{{ $tituloCancionSiendoEliminada }}**. 
                    Esta acción no se puede deshacer.
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                
                <flux:button wire:click="eliminar" variant="danger">
                    Sí, eliminar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
