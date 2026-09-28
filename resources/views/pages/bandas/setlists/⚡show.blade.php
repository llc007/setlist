<?php

use App\Models\Banda;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Setlist;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;
    public Setlist $setlist;

    public bool $showAddSongModal = false;
    public bool $showEditSongModal = false;

    // Filtros para seleccionar canción
    public ?int $filtroCategoriaId = null;
    public string $searchCancion = '';

    // Campos para agregar canción
    public ?int $selectedCancionId = null;
    public string $cancionProposito = '';
    public string $cancionTono = '';
    public string $cancionObservacion = '';
    public string $cancionNota = '';

    // Campos para editar canción en setlist
    public ?int $editingPivotId = null;
    public string $editingCancionTitulo = '';
    public string $editingProposito = '';
    public string $editingTono = '';
    public string $editingObservacion = '';

    // Modal para compartir en WhatsApp
    public bool $showWhatsappModal = false;

    public function mount(Banda $banda, Setlist $setlist): void
    {
        $this->banda = $banda;
        $this->setlist = $setlist->load('canciones.categoria');
        session()->put('active_banda_id', $this->banda->id);
    }

    public function openWhatsappModal(): void
    {
        $this->setlist->load('canciones.categoria');
        $this->showWhatsappModal = true;
    }

    public function getWhatsappText(): string
    {
        $tipoIcon = match ($this->setlist->tipo) {
            'culto' => '🏛️',
            'ensayo' => '🎸',
            'presentacion' => '🎤',
            default => '📋'
        };

        $numberEmojis = ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];
        $fechaFormateada = $this->setlist->fecha ? $this->setlist->fecha->format('d/m/Y') : '';

        $lineas = [];
        $lineas[] = '🎶 *SETLIST: ' . mb_strtoupper($this->setlist->nombre) . '*';
        $lineas[] = '👥 *Banda:* ' . $this->banda->nombre;
        if ($fechaFormateada) {
            $lineas[] = '📅 *Fecha:* ' . $fechaFormateada;
        }
        $lineas[] = "{$tipoIcon} *Tipo:* " . ucfirst($this->setlist->tipo);

        if (! empty($this->setlist->descripcion)) {
            $lineas[] = '💬 *Nota:* ' . $this->setlist->descripcion;
        }

        $lineas[] = '';
        $lineas[] = '━━━━━━━━━━━━━━━━━━━';
        $lineas[] = '🎼 *ORDEN DE CANCIONES (' . $this->setlist->canciones->count() . ')*';
        $lineas[] = '━━━━━━━━━━━━━━━━━━━';
        $lineas[] = '';

        if ($this->setlist->canciones->isEmpty()) {
            $lineas[] = '_(Aún no hay canciones en este setlist)_';
        } else {
            foreach ($this->setlist->canciones as $idx => $cancion) {
                $num = $numberEmojis[$idx] ?? (($idx + 1) . '.');
                $lineas[] = "{$num} *{$cancion->titulo}*";

                $detalles = [];
                if (! empty($cancion->pivot->proposito)) {
                    $detalles[] = '🎯 *Propósito:* ' . $cancion->pivot->proposito;
                }

                $tono = $cancion->pivot->tono ?: $cancion->tono_original;
                if (! empty($tono)) {
                    $detalles[] = '🎹 *Tono:* ' . $tono;
                }

                if (! empty($cancion->categoria?->nombre)) {
                    $detalles[] = '🏷️ *' . $cancion->categoria->nombre . '*';
                }

                if (! empty($detalles)) {
                    $lineas[] = '    ' . implode('  |  ', $detalles);
                }

                $nota = $cancion->pivot->observacion ?: $cancion->pivot->nota;
                if (! empty($nota)) {
                    $lineas[] = '    📝 *Obs:* ' . $nota;
                }

                $lineas[] = '';
            }
        }

        $url = url('/setlist/' . $this->setlist->id);
        $lineas[] = '━━━━━━━━━━━━━━━━━━━';
        $lineas[] = '📲 *Ver Setlist en la App:*';
        $lineas[] = $url;

        return implode("\n", $lineas);
    }

    public function updatedFiltroCategoriaId(): void
    {
        // Si la canción actualmente seleccionada no pertenece a la categoría filtrada, la deseleccionamos
        if ($this->selectedCancionId && $this->filtroCategoriaId) {
            $cancion = Cancion::find($this->selectedCancionId);
            if ($cancion && $cancion->categoria_id != $this->filtroCategoriaId) {
                $this->selectedCancionId = null;
            }
        }
    }

    public function updatedSelectedCancionId($cancionId): void
    {
        if ($cancionId) {
            $cancion = Cancion::find($cancionId);
            if ($cancion && empty($this->cancionTono) && ! empty($cancion->tono_original)) {
                $this->cancionTono = $cancion->tono_original;
            }
        }
    }

    public function openAddSongModal(): void
    {
        $this->selectedCancionId = null;
        $this->filtroCategoriaId = null;
        $this->searchCancion = '';
        $this->cancionProposito = '';
        $this->cancionTono = '';
        $this->cancionObservacion = '';
        $this->cancionNota = '';
        $this->showAddSongModal = true;
    }

    public function addCancion(): void
    {
        if (! $this->selectedCancionId) {
            return;
        }

        $maxOrden = $this->setlist->canciones()->max('cancion_setlist.orden') ?? 0;
        $observacion = $this->cancionObservacion ?: ($this->cancionNota ?: null);

        $this->setlist->canciones()->attach($this->selectedCancionId, [
            'orden' => $maxOrden + 1,
            'proposito' => $this->cancionProposito ?: null,
            'tono' => $this->cancionTono ?: null,
            'observacion' => $observacion,
            'nota' => $observacion,
        ]);

        $this->setlist->load('canciones');
        $this->showAddSongModal = false;
        Flux::toast(text: 'Canción agregada al setlist.', variant: 'success');
    }

    public function openEditSongModal(int $pivotId): void
    {
        $cancion = $this->setlist->canciones->first(fn ($c) => $c->pivot->id == $pivotId);
        if (! $cancion) {
            return;
        }

        $this->editingPivotId = $pivotId;
        $this->editingCancionTitulo = $cancion->titulo;
        $this->editingProposito = $cancion->pivot->proposito ?? '';
        $this->editingTono = $cancion->pivot->tono ?? ($cancion->tono_original ?? '');
        $this->editingObservacion = $cancion->pivot->observacion ?? ($cancion->pivot->nota ?? '');
        $this->showEditSongModal = true;
    }

    public function updateCancionDetalles(): void
    {
        if (! $this->editingPivotId) {
            return;
        }

        $cancion = $this->setlist->canciones->first(fn ($c) => $c->pivot->id == $this->editingPivotId);
        if ($cancion) {
            $cancion->pivot->proposito = $this->editingProposito ?: null;
            $cancion->pivot->tono = $this->editingTono ?: null;
            $cancion->pivot->observacion = $this->editingObservacion ?: null;
            $cancion->pivot->nota = $this->editingObservacion ?: null;
            $cancion->pivot->save();

            $this->setlist->load('canciones');
            $this->showEditSongModal = false;
            Flux::toast(text: 'Detalles actualizados correctamente.', variant: 'success');
        }
    }

    public function removeCancion(int $cancionId): void
    {
        $this->setlist->canciones()->detach($cancionId);
        $this->setlist->load('canciones');
        Flux::toast(text: 'Canción removida del setlist.', variant: 'success');
    }

    public function reorderCanciones(array $orderedPivotIds): void
    {
        foreach ($orderedPivotIds as $index => $pivotId) {
            \Illuminate\Support\Facades\DB::table('cancion_setlist')
                ->where('id', $pivotId)
                ->where('setlist_id', $this->setlist->id)
                ->update(['orden' => $index + 1]);
        }

        $this->setlist->load('canciones.categoria');
        Flux::toast(text: 'Orden del setlist actualizado.', variant: 'success', duration: 2000);
    }

    public function moveUp(int $pivotId): void
    {
        $canciones = $this->setlist->canciones;
        $currentIndex = $canciones->search(fn ($c) => $c->pivot->id == $pivotId);

        if ($currentIndex !== false && $currentIndex > 0) {
            $prevCancion = $canciones[$currentIndex - 1];
            $currentCancion = $canciones[$currentIndex];

            $tempOrden = $currentCancion->pivot->orden;
            $currentCancion->pivot->orden = $prevCancion->pivot->orden;
            $prevCancion->pivot->orden = $tempOrden;

            $currentCancion->pivot->save();
            $prevCancion->pivot->save();

            $this->setlist->load('canciones');
        }
    }

    public function moveDown(int $pivotId): void
    {
        $canciones = $this->setlist->canciones;
        $currentIndex = $canciones->search(fn ($c) => $c->pivot->id == $pivotId);

        if ($currentIndex !== false && $currentIndex < $canciones->count() - 1) {
            $nextCancion = $canciones[$currentIndex + 1];
            $currentCancion = $canciones[$currentIndex];

            $tempOrden = $currentCancion->pivot->orden;
            $currentCancion->pivot->orden = $nextCancion->pivot->orden;
            $nextCancion->pivot->orden = $tempOrden;

            $currentCancion->pivot->save();
            $nextCancion->pivot->save();

            $this->setlist->load('canciones');
        }
    }

    public function render(): mixed
    {
        // Canciones de la banda que no están en este setlist
        $existingCancionIds = $this->setlist->canciones->pluck('id')->toArray();

        $bandaCancionesQuery = $this->banda->repertorio()
            ->with('categoria')
            ->whereNotIn('canciones.id', $existingCancionIds);

        if ($this->filtroCategoriaId) {
            $bandaCancionesQuery->where('categoria_id', $this->filtroCategoriaId);
        }

        if ($this->searchCancion) {
            $bandaCancionesQuery->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->searchCancion . '%')
                    ->orWhere('artista', 'like', '%' . $this->searchCancion . '%')
                    ->orWhere('codigo', 'like', '%' . $this->searchCancion . '%');
            });
        }

        $bandaCanciones = $bandaCancionesQuery->orderBy('titulo')->get();

        // Categorías ordenadas alfabéticamente
        $categorias = Categoria::orderBy('nombre')->get();

        $selectedCancion = $this->selectedCancionId ? Cancion::with('categoria')->find($this->selectedCancionId) : null;

        return view('pages.bandas.setlists.⚡show', [
            'bandaCanciones' => $bandaCanciones,
            'categorias' => $categorias,
            'selectedCancion' => $selectedCancion,
        ])->title($this->setlist->nombre . ' - ' . $this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado de Setlist -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">{{ $setlist->nombre }}</flux:heading>
                <flux:badge color="{{ match($setlist->tipo) { 'culto' => 'purple', 'ensayo' => 'amber', 'presentacion' => 'sky', default => 'zinc' } }}" size="sm">
                    {{ Str::headline($setlist->tipo) }}
                </flux:badge>
            </div>

            <flux:text variant="subtle" class="mt-1">
                {{ __('Fecha: ') . $setlist->fecha->format('d/m/Y') }} • {{ __('Banda: ') . $banda->nombre }}
            </flux:text>

            @if ($setlist->descripcion)
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">{{ $setlist->descripcion }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <flux:button variant="outline" icon="arrow-left" :href="route('bandas.setlists.index', $banda->slug)" wire:navigate>
                {{ __('Volver') }}
            </flux:button>
            <button
                type="button"
                wire:click="openWhatsappModal"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#25D366] hover:bg-[#20ba5a] active:bg-[#1da851] text-white text-xs font-bold shadow-sm transition-colors cursor-pointer"
            >
                <svg class="size-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.825 2.791.825 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.771-5.767-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.077-2.135-.519-1.839-.762-3.031-2.616-3.123-2.738-.092-.122-.746-.992-.746-1.892 0-.9.472-1.344.64-1.528.168-.184.366-.23.488-.23.122 0 .244.001.351.006.113.006.265-.043.414.316.153.366.519 1.266.565 1.358.046.092.077.199.015.321-.061.122-.092.199-.183.306-.092.107-.193.24-.275.322-.092.092-.188.192-.081.376.107.184.475.784 1.019 1.269.701.625 1.292.818 1.476.91.184.092.29.077.397-.046.107-.122.458-.535.58-.718.122-.184.244-.153.412-.092.168.061 1.069.504 1.252.596.183.092.306.138.351.214.045.077.045.444-.099.849zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.435 5.176L2 22l4.958-1.399C8.384 21.464 10.129 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                </svg>
                <span>{{ __('Compartir WhatsApp') }}</span>
            </button>
            <flux:button variant="primary" icon="plus" wire:click="openAddSongModal">
                {{ __('Agregar Canción') }}
            </flux:button>
        </div>
    </div>

    <!-- Lista Ordenada de Canciones del Setlist con Drag & Drop -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <flux:heading size="lg">{{ __('Orden del Setlist (' . $setlist->canciones->count() . ' canciones)') }}</flux:heading>
                @if ($setlist->canciones->count() > 1)
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 flex items-center gap-1.5">
                        <span>✨ Arrastra las canciones desde el ícono</span>
                        <svg class="size-3.5 fill-current inline text-zinc-400" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm-7 7.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm-7 7.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                        <span>para ordenar rápidamente.</span>
                    </p>
                @endif
            </div>
        </div>

        <div
            x-data="{
                initSortable() {
                    if (typeof Sortable === 'undefined') return;
                    new Sortable(this.$refs.sortableContainer, {
                        handle: '.drag-handle',
                        animation: 200,
                        ghostClass: '!opacity-25',
                        chosenClass: '!scale-[1.01]',
                        dragClass: '!shadow-2xl',
                        onEnd: () => {
                            const ids = Array.from(this.$refs.sortableContainer.querySelectorAll('[data-pivot-id]'))
                                .map(el => parseInt(el.getAttribute('data-pivot-id')));
                            $wire.reorderCanciones(ids);
                        }
                    });
                }
            }"
            x-init="initSortable()"
            x-ref="sortableContainer"
            class="space-y-3"
        >
            @forelse ($setlist->canciones as $index => $cancion)
                <div
                    wire:key="setlist-item-{{ $cancion->pivot->id }}"
                    data-pivot-id="{{ $cancion->pivot->id }}"
                    class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-150"
                >
                    <div class="flex items-start gap-3 sm:gap-3.5">
                        <!-- Manilla de arrastre (Drag handle) -->
                        <div
                            class="drag-handle flex items-center justify-center p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200/70 dark:hover:bg-zinc-700/70 transition cursor-grab active:cursor-grabbing shrink-0 select-none touch-none mt-0.5"
                            title="Haz clic y arrastra para cambiar el orden"
                        >
                            <svg class="size-5 fill-current" viewBox="0 0 24 24">
                                <path d="M8.5 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm-7 7.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm-7 7.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                            </svg>
                        </div>

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm mt-0.5 shadow-sm">
                            {{ $index + 1 }}
                        </span>

                        <div class="space-y-1">
                            <div class="font-bold text-base text-zinc-900 dark:text-zinc-100 flex flex-wrap items-center gap-2">
                                <span>{{ $cancion->titulo }}</span>

                                @if ($cancion->pivot->proposito)
                                    <flux:badge color="indigo" size="sm" icon="flag">
                                        {{ $cancion->pivot->proposito }}
                                    </flux:badge>
                                @endif

                                @if ($cancion->pivot->tono)
                                    <flux:badge color="amber" size="sm" icon="musical-note">
                                        Tono: {{ $cancion->pivot->tono }}
                                        @if ($cancion->tono_original && $cancion->tono_original !== $cancion->pivot->tono)
                                            <span class="text-zinc-400 font-normal ml-1">({{ $cancion->tono_original }})</span>
                                        @endif
                                    </flux:badge>
                                @elseif ($cancion->tono_original)
                                    <flux:badge color="zinc" size="sm">Tono: {{ $cancion->tono_original }}</flux:badge>
                                @endif
                            </div>

                            <div class="text-xs text-zinc-500">{{ $cancion->artista ?? 'Sin artista' }}</div>

                            @if ($cancion->pivot->observacion || $cancion->pivot->nota)
                                <div class="mt-1 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-md px-2.5 py-1 inline-flex items-center gap-1.5 font-medium">
                                    <span>📝 {{ $cancion->pivot->observacion ?: $cancion->pivot->nota }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-1.5 self-end sm:self-center">
                        <!-- Editar detalles de la canción en este setlist -->
                        <flux:button
                            variant="ghost"
                            icon="pencil-square"
                            size="sm"
                            wire:click="openEditSongModal({{ $cancion->pivot->id }})"
                            title="Editar Propósito, Tono y Observación"
                        />

                        <!-- Ver canción -->
                        <flux:button
                            variant="ghost"
                            icon="eye"
                            size="sm"
                            :href="route('ver-cancion', $cancion->id)"
                            wire:navigate
                            title="Ver Acordes"
                        />

                        <!-- Eliminar del setlist -->
                        <flux:button
                            variant="ghost"
                            icon="trash"
                            size="sm"
                            class="text-red-500 hover:text-red-700"
                            wire:click="removeCancion({{ $cancion->id }})"
                            title="Quitar del Setlist"
                        />
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-zinc-500">
                    <flux:icon.musical-note class="size-10 mx-auto text-zinc-400 mb-2" />
                    <p>{{ __('Este setlist aún no contiene canciones. Haz clic en "Agregar Canción" para construir tu lista.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Agregar Canción al Setlist -->
    <flux:modal wire:model="showAddSongModal" name="add-song-modal" class="max-w-lg">
        <form wire:submit="addCancion" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Agregar Canción al Setlist') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Filtra por categoría o busca por título, y personaliza su propósito y tono.') }}</flux:text>
            </div>

            <!-- Filtro de Categoría y Búsqueda -->
            <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:select wire:model.live="filtroCategoriaId" label="Filtrar por Categoría">
                            <flux:select.option value="">{{ __('Todas las categorías') }}</flux:select.option>
                            @foreach ($categorias as $cat)
                                <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:input
                            wire:model.live.debounce.250ms="searchCancion"
                            label="Buscar Canción"
                            placeholder="Título, artista o código..."
                            clearable
                        />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <flux:select wire:model.live="selectedCancionId" label="Canción ({{ $bandaCanciones->count() }} disponibles)" required>
                        <flux:select.option value="">{{ __('Selecciona una canción...') }}</flux:select.option>
                        @foreach ($bandaCanciones as $bCancion)
                            <flux:select.option value="{{ $bCancion->id }}">
                                {{ $bCancion->titulo }} @if($bCancion->categoria) • [{{ $bCancion->categoria->nombre }}] @endif ({{ $bCancion->artista ?? 'Sin artista' }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @if ($bandaCanciones->isEmpty())
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            ⚠️ No se encontraron canciones con ese filtro en el repertorio de la banda.
                        </p>
                    @endif
                </div>

                <!-- Tarjeta de previsualización de canción seleccionada -->
                @if ($selectedCancion)
                    <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/60 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $selectedCancion->titulo }}</div>
                            <div class="text-zinc-500">{{ $selectedCancion->artista ?? 'Sin artista' }}</div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if ($selectedCancion->categoria)
                                <flux:badge color="indigo" size="sm">{{ $selectedCancion->categoria->nombre }}</flux:badge>
                            @endif
                            @if ($selectedCancion->tono_original)
                                <flux:badge color="amber" size="sm">Tono Original: {{ $selectedCancion->tono_original }}</flux:badge>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Propósito con sugerencias rápidas -->
                <div class="space-y-1.5">
                    <flux:input
                        wire:model="cancionProposito"
                        label="Propósito (Opcional)"
                        placeholder="Ej: Inicio, Especial Coro 1, Especial ofrenda..."
                    />
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Sugerencias:</span>
                        @foreach (['Inicio', 'Especial Coro 1', 'Especial ofrenda', 'Alabanza', 'Adoración', 'Ministración', 'Cierre'] as $prop)
                            <button
                                type="button"
                                wire:click="$set('cancionProposito', '{{ $prop }}')"
                                class="text-xs px-2 py-0.5 rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                            >
                                {{ $prop }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Tono (opcional) -->
                <flux:input
                    wire:model="cancionTono"
                    label="Tono (Opcional)"
                    placeholder="Ej: A, G, C#m (por defecto tono original)"
                />

                <!-- Observación -->
                <flux:textarea
                    wire:model="cancionObservacion"
                    label="Observación"
                    placeholder="Ej: Solo de guitarra en intro, coro x2, modulación..."
                    rows="2"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:button variant="outline" wire:click="$set('showAddSongModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Agregar al Setlist') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Editar Detalles de Canción en Setlist -->
    <flux:modal wire:model="showEditSongModal" name="edit-song-modal" class="max-w-md">
        <form wire:submit="updateCancionDetalles" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Editar Detalles de Canción') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Modificando: ') }}<strong>{{ $editingCancionTitulo }}</strong>
                </flux:text>
            </div>

            <div class="space-y-4">
                <!-- Propósito con sugerencias rápidas -->
                <div class="space-y-1.5">
                    <flux:input
                        wire:model="editingProposito"
                        label="Propósito (Opcional)"
                        placeholder="Ej: Inicio, Especial Coro 1, Especial ofrenda..."
                    />
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Sugerencias:</span>
                        @foreach (['Inicio', 'Especial Coro 1', 'Especial ofrenda', 'Alabanza', 'Adoración', 'Ministración', 'Cierre'] as $prop)
                            <button
                                type="button"
                                wire:click="$set('editingProposito', '{{ $prop }}')"
                                class="text-xs px-2 py-0.5 rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                            >
                                {{ $prop }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Tono (opcional) -->
                <flux:input
                    wire:model="editingTono"
                    label="Tono (Opcional)"
                    placeholder="Ej: A, G, C#m..."
                />

                <!-- Observación -->
                <flux:textarea
                    wire:model="editingObservacion"
                    label="Observación"
                    placeholder="Ej: Solo de guitarra en intro, coro x2..."
                    rows="2"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:button variant="outline" wire:click="$set('showEditSongModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Guardar Cambios') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Compartir por WhatsApp -->
    <flux:modal wire:model="showWhatsappModal" name="whatsapp-modal" class="max-w-xl">
        @php
            $whatsappTexto = $this->getWhatsappText();
            $whatsappUrlApi = 'https://api.whatsapp.com/send?text=' . rawurlencode($whatsappTexto);
            $whatsappUrlProtocol = 'whatsapp://send?text=' . rawurlencode($whatsappTexto);
            $publicSetlistUrl = url('/setlist/' . $this->setlist->id);
            $isLocalhost = str_contains($publicSetlistUrl, 'localhost') || str_contains($publicSetlistUrl, '127.0.0.1');
        @endphp

        <div class="space-y-5" x-data="{
            copiado: false,
            copiadoLink: false,
            compartirDirecto() {
                const texto = $refs.whatsappText.value;
                if (navigator.share) {
                    navigator.share({
                        title: 'Setlist: {{ addslashes($setlist->nombre) }}',
                        text: texto,
                    }).catch(() => {});
                } else {
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(texto), '_blank');
                }
            }
        }">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-[#25D366]">
                    <svg class="size-6 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.825 2.791.825 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.771-5.767-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.077-2.135-.519-1.839-.762-3.031-2.616-3.123-2.738-.092-.122-.746-.992-.746-1.892 0-.9.472-1.344.64-1.528.168-.184.366-.23.488-.23.122 0 .244.001.351.006.113.006.265-.043.414.316.153.366.519 1.266.565 1.358.046.092.077.199.015.321-.061.122-.092.199-.183.306-.092.107-.193.24-.275.322-.092.092-.188.192-.081.376.107.184.475.784 1.019 1.269.701.625 1.292.818 1.476.91.184.092.29.077.397-.046.107-.122.458-.535.58-.718.122-.184.244-.153.412-.092.168.061 1.069.504 1.252.596.183.092.306.138.351.214.045.077.045.444-.099.849zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.435 5.176L2 22l4.958-1.399C8.384 21.464 10.129 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                    </svg>
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Compartir Setlist en WhatsApp') }}</flux:heading>
                    <flux:text variant="subtle">{{ __('Envía el repertorio formateado al grupo de WhatsApp de tu coro o banda.') }}</flux:text>
                </div>
            </div>

            <!-- Vista previa del mensaje formateado -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="font-medium">{{ __('Vista previa del mensaje:') }}</span>
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText($refs.whatsappText.value); copiado = true; setTimeout(() => copiado = false, 2500)"
                        class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer"
                    >
                        <flux:icon.document-duplicate class="size-3.5" />
                        <span x-text="copiado ? '¡Copiado!' : 'Copiar texto'"></span>
                    </button>
                </div>

                <div class="relative rounded-2xl bg-[#efeae2] dark:bg-[#0b141a] p-3 sm:p-4 border border-[#e2dcd5] dark:border-zinc-800 shadow-inner max-h-64 overflow-y-auto">
                    <!-- Burbuja de chat simulando WhatsApp -->
                    <div class="rounded-xl bg-[#dcf8c6] dark:bg-[#005c4b] text-zinc-900 dark:text-[#e9edef] p-3.5 text-xs font-mono whitespace-pre-wrap leading-relaxed shadow-sm selection:bg-emerald-300 dark:selection:bg-emerald-800">
{{ $whatsappTexto }}
                    </div>
                </div>

                <textarea x-ref="whatsappText" class="sr-only" readonly>{{ $whatsappTexto }}</textarea>
            </div>

            <!-- Enlace público directo -->
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">🔗 Enlace público del Setlist (Sin login):</span>
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $publicSetlistUrl }}'); copiadoLink = true; setTimeout(() => copiadoLink = false, 2500)"
                        class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1 cursor-pointer"
                    >
                        <span x-text="copiadoLink ? '¡Enlace copiado!' : 'Copiar enlace'"></span>
                    </button>
                </div>
                <div class="font-mono text-xs text-zinc-600 dark:text-zinc-400 truncate select-all">
                    {{ $publicSetlistUrl }}
                </div>
                @if ($isLocalhost)
                    <p class="text-[11px] text-amber-700 dark:text-amber-300 leading-tight pt-1">
                        💡 <strong>Nota sobre localhost:</strong> Como estás en desarrollo local (<code class="bg-amber-100 dark:bg-amber-900/50 px-1 py-0.5 rounded text-[10px]">localhost</code>), los celulares de tu grupo no podrán abrir este enlace a menos que uses la IP de tu computador en la red Wi-Fi (ej. <code class="bg-amber-100 dark:bg-amber-900/50 px-1 py-0.5 rounded text-[10px]">http://192.168.x.x:8000</code>) o cuando el sistema esté subido a internet.
                    </p>
                @endif
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                <flux:button variant="outline" wire:click="$set('showWhatsappModal', false)">
                    {{ __('Cerrar') }}
                </flux:button>

                <div class="flex items-center gap-2 flex-wrap justify-end">
                    <!-- Botón Copiar Texto -->
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText($refs.whatsappText.value); copiado = true; setTimeout(() => copiado = false, 2500)"
                        class="px-3.5 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <flux:icon.document-duplicate class="size-4" />
                        <span x-text="copiado ? '¡Copiado!' : 'Copiar Texto'"></span>
                    </button>

                    <!-- Botón Principal: Compartir al Grupo (Activa selector de chats de WhatsApp) -->
                    <button
                        type="button"
                        x-on:click="compartirDirecto()"
                        class="px-4 py-2.5 rounded-lg bg-[#25D366] hover:bg-[#20ba5a] active:bg-[#1da851] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition cursor-pointer"
                        title="Abre el selector de chats de WhatsApp para elegir tu grupo"
                    >
                        <svg class="size-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.825 2.791.825 3.181 0 5.767-2.586 5.767-5.766.001-3.182-2.585-5.771-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.077-2.135-.519-1.839-.762-3.031-2.616-3.123-2.738-.092-.122-.746-.992-.746-1.892 0-.9.472-1.344.64-1.528.168-.184.366-.23.488-.23.122 0 .244.001.351.006.113.006.265-.043.414.316.153.366.519 1.266.565 1.358.046.092.077.199.015.321-.061.122-.092.199-.183.306-.092.107-.193.24-.275.322-.092.092-.188.192-.081.376.107.184.475.784 1.019 1.269.701.625 1.292.818 1.476.91.184.092.29.077.397-.046.107-.122.458-.535.58-.718.122-.184.244-.153.412-.092.168.061 1.069.504 1.252.596.183.092.306.138.351.214.045.077.045.444-.099.849zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.435 5.176L2 22l4.958-1.399C8.384 21.464 10.129 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                        </svg>
                        <span>{{ __('Elegir Grupo en WhatsApp') }}</span>
                    </button>

                    <!-- Abrir directamente por protocolo WhatsApp Desktop / Web fallback -->
                    <a
                        href="{{ $whatsappUrlApi }}"
                        target="_blank"
                        class="px-3.5 py-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold flex items-center gap-1.5 transition"
                        title="Abrir a través de WhatsApp Web"
                    >
                        <span>WhatsApp Web</span>
                    </a>
                </div>
            </div>
        </div>
    </flux:modal>

    <script src="{{ asset('vendor/sortable.min.js') }}"></script>
</div>
