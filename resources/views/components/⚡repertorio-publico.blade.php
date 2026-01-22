<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $categoria_id = '';

    // Reseteamos la página cuando cambia el buscador
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Pasamos los datos a la vista
    public function with()
    {
        return [
            'canciones' => Cancion::query()
                ->with('recursos', 'categoria')
                ->when($this->search, fn($q) => $q->where('titulo', 'like', "%{$this->search}%"))
                ->when($this->categoria_id, fn($q) => $q->where('categoria_id', $this->categoria_id))
                ->orderBy('titulo')
                ->paginate(12),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ];
    }
}; ?>

<div class="p-4 md:p-8 max-w-4xl mx-auto">
    <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Biblioteca de Canciones</h1>

    <div class="w-full">
    <label class="flex flex-col w-full h-12 relative group">
        <div class="flex w-full flex-1 items-stretch rounded-lg h-full bg-white dark:bg-[#283039] border border-slate-200 dark:border-transparent group-focus-within:ring-2 ring-primary/50 transition-all shadow-sm">
            
            <div wire:loading.remove wire:target="search" class="text-slate-400 dark:text-[#9dabb9] flex items-center justify-center pl-4 pr-2">
                <span class="material-symbols-outlined">search</span>
            </div>

            <div wire:loading.flex wire:target="search" class="text-slate-400 dark:text-[#9dabb9] items-center justify-center pl-4 pr-2">
                <span class="material-symbols-outlined animate-spin text-primary text-xl">progress_activity</span>
            </div>

            <input 
                wire:model.live.debounce.300ms="search"
                class="flex w-full min-w-0 flex-1 resize-none overflow-hidden bg-transparent border-none text-slate-900 dark:text-white focus:outline-0 focus:ring-0 h-full placeholder:text-slate-400 dark:placeholder:text-[#9dabb9] px-2 text-base font-normal leading-normal" 
                placeholder="Buscar por título o categoría..."
            />

            <div class="hidden sm:flex items-center pr-3">
                <span class="text-xs text-slate-400 border border-slate-200 dark:border-slate-600 rounded px-1.5 py-0.5">/</span>
            </div>
        </div>
    </label>
</div>

    <div class="mt-6 flex flex-col md:row gap-4">
        <flux:select wire:model.live="categoria_id" placeholder="Todas las categorías" class="md:w-64">
            <flux:select.option value="">Todas</flux:select.option>
            @foreach($categorias as $cat)
                <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="mt-8 space-y-4">
    @forelse($canciones as $cancion)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">
                        {{ $cancion->titulo }}
                    </h3>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                            {{ $cancion->categoria->nombre }}
                        </span>
                    </div>
                </div>
            </div>

            @if($cancion->recursos->count() > 0)
                <div class="mt-4 flex flex-wrap gap-2 pt-4 border-t border-slate-50 dark:border-slate-800">
                    @foreach($cancion->recursos as $recurso)
                        <a 
                            href="{{ $recurso->url }}" 
                            target="_blank" 
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors border border-primary/20"
                        >
                            <flux:icon.{{ $recurso->tipo === 'youtube' ? 'video-camera' : 'document-text' }} class="size-4" />
                            {{ $recurso->etiqueta }}
                        </a>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-xs text-slate-400 italic font-light">Sin recursos cargados</p>
            @endif
        </div>
    @empty
        <div class="text-center py-12 bg-slate-50 dark:bg-white/5 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800">
            <p class="text-slate-500 dark:text-slate-400">No encontramos canciones.</p>
        </div>
    @endforelse
</div>

    <div class="mt-6">
        {{ $canciones->links() }}
    </div>
</div>  