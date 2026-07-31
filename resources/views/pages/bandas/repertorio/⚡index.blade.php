<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    use WithPagination;

    public Banda $banda;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $categoriaId = '';

    #[Url]
    public string $visibilidad = ''; // '', 'publica', 'privada'

    // Modales
    public bool $showCreateModal = false;
    public bool $showImportModal = false;

    // Campos Nueva Canción
    public string $titulo = '';
    public string $artista = '';
    public string $letra = '';
    public string $tono_original = '';
    public string $categoria_id = '';
    public bool $es_publica = true;

    // Búsqueda para importar
    public string $importSearch = '';
    public array $selectedImportIds = [];

    public function mount(Banda $banda): void
    {
        $this->banda = $banda;
        session()->put('active_banda_id', $this->banda->id);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoriaId(): void
    {
        $this->resetPage();
    }

    public function updatedVisibilidad(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->titulo = '';
        $this->artista = '';
        $this->letra = '';
        $this->tono_original = '';
        $this->categoria_id = '';
        $this->es_publica = true;
        $this->showCreateModal = true;
    }

    public function saveCancion(): void
    {
        $validated = $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'artista' => ['nullable', 'string', 'max:255'],
            'letra' => ['nullable', 'string'],
            'tono_original' => ['nullable', 'string', 'max:10'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'es_publica' => ['boolean'],
        ]);

        $cancion = Cancion::create([
            'titulo' => $validated['titulo'],
            'artista' => $validated['artista'] ?? null,
            'letra' => $validated['letra'] ?? null,
            'tono_original' => $validated['tono_original'] ?? null,
            'categoria_id' => ! empty($validated['categoria_id']) ? $validated['categoria_id'] : null,
            'banda_id' => $this->banda->id,
            'es_publica' => $validated['es_publica'],
        ]);

        // Vincular a la banda
        $this->banda->repertorio()->attach($cancion->id);

        session()->flash('status', 'Canción agregada al repertorio de la banda.');
        $this->showCreateModal = false;
    }

    public function openImportModal(): void
    {
        $this->importSearch = '';
        $this->selectedImportIds = [];
        $this->showImportModal = true;
    }

    public function importSelected(): void
    {
        if (empty($this->selectedImportIds)) {
            return;
        }

        $existingIds = $this->banda->repertorio()->pluck('canciones.id')->toArray();
        $newIds = array_diff($this->selectedImportIds, $existingIds);

        if (! empty($newIds)) {
            $this->banda->repertorio()->attach($newIds);
            session()->flash('status', count($newIds) . ' canciones importadas al repertorio.');
        }

        $this->showImportModal = false;
    }

    public function removeFromRepertoire(int $cancionId): void
    {
        $this->banda->repertorio()->detach($cancionId);
        session()->flash('status', 'Canción removida del repertorio de la banda.');
    }

    public function render(): mixed
    {
        $repertorioQuery = $this->banda->repertorio()
            ->with('categoria')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('titulo', 'like', '%' . $this->search . '%')
                        ->orWhere('artista', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoriaId, fn ($q) => $q->where('categoria_id', $this->categoriaId))
            ->when($this->visibilidad !== '', function ($q) {
                if ($this->visibilidad === 'publica') {
                    $q->where('es_publica', true);
                } elseif ($this->visibilidad === 'privada') {
                    $q->where('es_publica', false);
                }
            });

        $canciones = $repertorioQuery->paginate(12);
        $categorias = Categoria::orderBy('nombre')->get();

        // Canciones públicas globales disponibles para importar
        $existingBandaCanciones = $this->banda->repertorio()->pluck('canciones.id')->toArray();
        $importableCanciones = Cancion::query()
            ->where('es_publica', true)
            ->whereNotIn('id', $existingBandaCanciones)
            ->when($this->importSearch, function ($q) {
                $q->where('titulo', 'like', '%' . $this->importSearch . '%')
                    ->orWhere('artista', 'like', '%' . $this->importSearch . '%');
            })
            ->take(20)
            ->get();

        return view('pages.bandas.repertorio.⚡index', [
            'canciones' => $canciones,
            'categorias' => $categorias,
            'importableCanciones' => $importableCanciones,
        ])->title('Repertorio - ' . $this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Repertorio de ') . $this->banda->nombre }}</flux:heading>
            <flux:text variant="subtle">{{ __('Gestiona las canciones públicas y privadas pertenecientes al repertorio de esta banda.') }}</flux:text>
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="arrow-down-tray" wire:click="openImportModal">
                {{ __('Importar Canción Pública') }}
            </flux:button>
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('Nueva Canción') }}
            </flux:button>
        </div>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Buscar por título o artista..."
                clearable
            />
        </div>

        <div class="w-full sm:w-56">
            <flux:select wire:model.live="categoriaId" placeholder="Categoría: Todas">
                <flux:select.option value="">{{ __('Todas las Categorías') }}</flux:select.option>
                @foreach ($categorias as $cat)
                    <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full sm:w-48">
            <flux:select wire:model.live="visibilidad" placeholder="Visibilidad: Todas">
                <flux:select.option value="">{{ __('Todas') }}</flux:select.option>
                <flux:select.option value="publica">{{ __('Públicas') }}</flux:select.option>
                <flux:select.option value="privada">{{ __('Privadas') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tabla de Canciones del Repertorio -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="first:!ps-6">{{ __('Título y Artista') }}</flux:table.column>
                <flux:table.column>{{ __('Categoría') }}</flux:table.column>
                <flux:table.column>{{ __('Tono Original') }}</flux:table.column>
                <flux:table.column>{{ __('Visibilidad') }}</flux:table.column>
                <flux:table.column align="center" class="last:!pe-6">{{ __('Acciones') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($canciones as $cancion)
                    <flux:table.row wire:key="banda-cancion-{{ $cancion->id }}">
                        <flux:table.cell class="first:!ps-6 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="font-semibold">{{ $cancion->titulo }}</div>
                            <div class="text-xs text-zinc-500">{{ $cancion->artista ?? 'Sin artista' }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm">{{ $cancion->categoria->nombre ?? 'General' }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="font-medium text-xs">
                            {{ $cancion->tono_original ?? 'N/A' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $cancion->es_publica ? 'sky' : 'amber' }}" size="sm">
                                {{ $cancion->es_publica ? 'Pública' : 'Privada de Banda' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="last:!pe-6">
                            <div class="flex items-center justify-center gap-2">
                                <flux:button
                                    variant="ghost"
                                    icon="eye"
                                    size="sm"
                                    :href="route('ver-cancion', $cancion->id)"
                                    wire:navigate
                                    title="Ver detalles"
                                />

                                <flux:button
                                    variant="ghost"
                                    icon="minus-circle"
                                    size="sm"
                                    class="text-red-500 hover:text-red-700"
                                    wire:click="removeFromRepertoire({{ $cancion->id }})"
                                    title="Quitar del repertorio"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                            {{ __('No hay canciones en el repertorio de la banda.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $canciones->links() }}
        </div>
    </div>

    <!-- Modal Crear Nueva Canción -->
    <flux:modal wire:model="showCreateModal" name="create-cancion-modal" class="max-w-lg">
        <form wire:submit="saveCancion" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Añadir Nueva Canción al Repertorio') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Puedes crear la canción como pública para todo el sistema o privada solo para tu banda.') }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="titulo" label="Título de la Canción" required />
                <flux:input wire:model="artista" label="Artista / Autor" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="categoria_id" label="Categoría">
                        <flux:select.option value="">{{ __('Sin categoría') }}</flux:select.option>
                        @foreach ($categorias as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="tono_original" label="Tono Original" placeholder="Ej: G, C, F#m" />
                </div>

                <flux:textarea wire:model="letra" label="Letra y Acordes" rows="4" placeholder="Ingresa la letra..." />

                <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    <flux:checkbox wire:model="es_publica" label="Hacer canción pública para todo el directorio" />
                    <flux:text variant="subtle" class="text-xs mt-1 block">
                        Si desmarcas esta opción, la canción será privada y solo la podrán ver los miembros de {{ $banda->nombre }}.
                    </flux:text>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showCreateModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Guardar Canción') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Importar Canción Pública -->
    <flux:modal wire:model="showImportModal" name="import-cancion-modal" class="max-w-xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Importar Canciones del Catálogo Público') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Busca y selecciona canciones globales para vincularlas al repertorio de ' . $banda->nombre) }}</flux:text>
            </div>

            <flux:input wire:model.live.debounce.300ms="importSearch" icon="magnifying-glass" placeholder="Buscar canción pública..." />

            <div class="max-h-60 overflow-y-auto space-y-2 border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                @forelse ($importableCanciones as $importCancion)
                    <label wire:key="import-{{ $importCancion->id }}" class="flex items-center justify-between p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded cursor-pointer">
                        <div class="flex items-center gap-3">
                            <flux:checkbox wire:model="selectedImportIds" value="{{ $importCancion->id }}" />
                            <div>
                                <div class="font-semibold text-sm">{{ $importCancion->titulo }}</div>
                                <div class="text-xs text-zinc-500">{{ $importCancion->artista ?? 'Sin artista' }}</div>
                            </div>
                        </div>
                        <flux:badge size="sm" color="zinc">{{ $importCancion->tono_original ?? 'N/A' }}</flux:badge>
                    </label>
                @empty
                    <div class="text-center py-6 text-zinc-500 text-sm">
                        {{ __('No hay canciones disponibles para importar.') }}
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showImportModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="primary" wire:click="importSelected">
                    {{ __('Importar Seleccionadas') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
