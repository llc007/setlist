<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $categoria_id = '';
    public $tono = '';

    // Reseteamos la página cuando cambia el buscador
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Pasamos los datos a la vista
    public function with()
    {
        return [
            'canciones' => Cancion::query()->with('recursos', 'categoria')->when($this->search, fn($q) => $q->where('titulo', 'like', "%{$this->search}%"))->when($this->categoria_id, fn($q) => $q->where('categoria_id', $this->categoria_id))->when($this->tono, fn($q) => $q->where('tono_original', $this->tono))->orderBy('titulo')->paginate(12),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tonos' => ['A', 'A#', 'Ab', 'B', 'Bb', 'C', 'C#', 'D', 'D#', 'Db', 'E', 'Eb', 'F', 'F#', 'G', 'G#', 'Gb'],
        ];
    }
}; ?>

<div class="p-4 md:p-12 w-full max-w-7xl mx-auto min-h-screen">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-slate-900 dark:text-white text-4xl md:text-5xl font-black leading-tight tracking-[-0.04em]">
                Biblioteca de Canciones</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">Gestiona el repertorio, letras y arreglos corales.
            </p>
        </div>
        <flux:button variant="primary" icon="plus" class="px-6 py-2.5 shadow-lg shadow-primary/20">Nueva Canción
        </flux:button>
    </div>

    <div class="w-full mb-6">
        <label class="flex flex-col w-full h-14 relative group">
            <div
                class="flex w-full flex-1 items-stretch rounded-xl h-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus-within:ring-2 ring-primary/20 transition-all shadow-sm">

                <div wire:loading.remove wire:target="search"
                    class="text-slate-400 flex items-center justify-center pl-5">
                    <flux:icon.magnifying-glass class="size-5" />
                </div>

                <div wire:loading.flex wire:target="search" class="text-slate-400 items-center justify-center pl-5">
                    <flux:icon.arrow-path class="size-5 animate-spin text-primary" />
                </div>

                <input wire:model.live.debounce.300ms="search"
                    class="flex w-full min-w-0 flex-1 bg-transparent border-none text-slate-900 dark:text-white focus:outline-0 focus:ring-0 h-full placeholder:text-slate-400 px-4 text-lg font-normal"
                    placeholder="Buscar por título, artista, tema o letra..." />

                <div class="hidden sm:flex items-center pr-5">
                    <kbd
                        class="text-xs text-slate-400 border border-slate-200 dark:border-slate-700 rounded px-2 py-1 font-sans bg-slate-50 dark:bg-slate-800">/</kbd>
                </div>
            </div>
        </label>
    </div>

    <div class="flex items-center gap-3 mb-6">
        <flux:select wire:model.live="categoria_id" icon="tag" placeholder="Tema: Todos"
            class="!bg-white dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 !rounded-full !px-4">
            <flux:select.option value="">Todos las categorías</flux:select.option>
            @foreach ($categorias as $cat)
                <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="tono" icon="musical-note"
            class="!rounded-full !bg-white dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 text-slate-600">
            <flux:select.option value="">Todos los tonos</flux:select.option>
            @foreach ($tonos as $t)
                <flux:select.option value="{{ $t }}">{{ $t }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid grid-cols-1 gap-2 mb-6">
        <div class="hidden md:flex items-center px-6 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">
            <div class="w-16"></div>
            <div class="flex-1">Título y Artista</div>
            <div class="w-48">Detalles</div>
            <div class="w-24 text-right">Acciones</div>
        </div>

        @forelse($canciones as $cancion)
            <div wire:key="cancion-{{ $cancion->id }}"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-primary/20 transition-all group">
                <div class="flex items-center gap-5">
                    <!-- Thumbnail placeholder with gradient -->
                    <div
                        class="size-12 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 flex-shrink-0 flex items-center justify-center text-white font-bold text-lg uppercase">
                        {{ Str::limit($cancion->categoria->nombre, 2, '') }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">
                            {{ $cancion->titulo }}
                        </h3>
                        <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">
                            {{ $cancion->categoria->nombre }}
                        </p>
                    </div>

                    <!-- Details Column -->
                    <div class="hidden md:flex flex-col gap-1 w-48 text-sm text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <flux:icon.musical-note class="size-4 text-slate-400" />
                            <span>Tono: {{ $cancion->tono_original ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1">
                        <flux:button variant="subtle" size="sm" icon="eye"
                            class="text-slate-400 hover:text-primary" />
                        <flux:button variant="subtle" size="sm" icon="plus-circle"
                            class="text-slate-400 hover:text-primary" />
                        <flux:button variant="subtle" size="sm" icon="pencil-square"
                            class="text-slate-400 hover:text-primary" />
                    </div>
                </div>
            </div>
        @empty
            <div
                class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                <flux:icon.musical-note class="size-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">No encontramos canciones en la
                    biblioteca.</p>
                <flux:button variant="subtle" class="mt-4">Ver todas las canciones</flux:button>
            </div>
        @endforelse
    </div>

    <div class="mt-8 flex justify-center">
        <flux:button variant="subtle"
            class="px-8 !rounded-xl !bg-white dark:!bg-slate-900 !border-slate-200 dark:!border-slate-800 font-bold text-slate-600">
            Cargar más canciones</flux:button>
    </div>

    <div class="mt-10">
        {{ $canciones->links() }}
    </div>
</div>
