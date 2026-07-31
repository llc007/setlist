<?php

use App\Models\Banda;
use App\Models\Setlist;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;
    public Setlist $setlist;

    public bool $showAddSongModal = false;
    public ?int $selectedCancionId = null;
    public string $cancionNota = '';

    public function mount(Banda $banda, Setlist $setlist): void
    {
        $this->banda = $banda;
        $this->setlist = $setlist->load('canciones');
        session()->put('active_banda_id', $this->banda->id);
    }

    public function openAddSongModal(): void
    {
        $this->selectedCancionId = null;
        $this->cancionNota = '';
        $this->showAddSongModal = true;
    }

    public function addCancion(): void
    {
        if (! $this->selectedCancionId) {
            return;
        }

        $maxOrden = $this->setlist->canciones()->max('cancion_setlist.orden') ?? 0;

        $this->setlist->canciones()->attach($this->selectedCancionId, [
            'orden' => $maxOrden + 1,
            'nota' => $this->cancionNota,
        ]);

        $this->setlist->load('canciones');
        $this->showAddSongModal = false;
        session()->flash('status', 'Canción agregada al setlist.');
    }

    public function removeCancion(int $cancionId): void
    {
        $this->setlist->canciones()->detach($cancionId);
        $this->setlist->load('canciones');
        session()->flash('status', 'Canción removida del setlist.');
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

        $bandaCanciones = $this->banda->repertorio()
            ->whereNotIn('canciones.id', $existingCancionIds)
            ->orderBy('titulo')
            ->get();

        return view('pages.bandas.setlists.⚡show', [
            'bandaCanciones' => $bandaCanciones,
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

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="arrow-left" :href="route('bandas.setlists.index', $banda->slug)" wire:navigate>
                {{ __('Volver a Setlists') }}
            </flux:button>
            <flux:button variant="primary" icon="plus" wire:click="openAddSongModal">
                {{ __('Agregar Canción') }}
            </flux:button>
        </div>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Lista Ordenada de Canciones del Setlist -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm space-y-4">
        <flux:heading size="lg">{{ __('Orden del Setlist (' . $setlist->canciones->count() . ' canciones)') }}</flux:heading>

        <div class="space-y-3">
            @forelse ($setlist->canciones as $index => $cancion)
                <div wire:key="setlist-item-{{ $cancion->pivot->id }}" class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">
                            {{ $index + 1 }}
                        </span>

                        <div>
                            <div class="font-bold text-base text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                {{ $cancion->titulo }}
                                @if ($cancion->tono_original)
                                    <flux:badge color="zinc" size="sm">Tono: {{ $cancion->tono_original }}</flux:badge>
                                @endif
                            </div>

                            <div class="text-xs text-zinc-500">{{ $cancion->artista ?? 'Sin artista' }}</div>

                            @if ($cancion->pivot->nota)
                                <div class="mt-1 text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    📌 {{ $cancion->pivot->nota }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Ordenar Arriba / Abajo -->
                        <flux:button
                            variant="ghost"
                            icon="chevron-up"
                            size="sm"
                            wire:click="moveUp({{ $cancion->pivot->id }})"
                            :disabled="$index === 0"
                        />
                        <flux:button
                            variant="ghost"
                            icon="chevron-down"
                            size="sm"
                            wire:click="moveDown({{ $cancion->pivot->id }})"
                            :disabled="$index === $setlist->canciones->count() - 1"
                        />

                        <!-- Ver canción -->
                        <flux:button
                            variant="ghost"
                            icon="eye"
                            size="sm"
                            :href="route('ver-cancion', $cancion->id)"
                            wire:navigate
                        />

                        <!-- Eliminar del setlist -->
                        <flux:button
                            variant="ghost"
                            icon="trash"
                            size="sm"
                            class="text-red-500 hover:text-red-700"
                            wire:click="removeCancion({{ $cancion->id }})"
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
    <flux:modal wire:model="showAddSongModal" name="add-song-modal" class="max-w-md">
        <form wire:submit="addCancion" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Agregar Canción al Setlist') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Selecciona una canción del repertorio de la banda.') }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:select wire:model="selectedCancionId" label="Canción" required>
                    <flux:select.option value="">{{ __('Selecciona una canción...') }}</flux:select.option>
                    @foreach ($bandaCanciones as $bCancion)
                        <flux:select.option value="{{ $bCancion->id }}">
                            {{ $bCancion->titulo }} ({{ $bCancion->artista ?? 'Sin artista' }})
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="cancionNota"
                    label="Nota / Observaciones para este Setlist (Opcional)"
                    placeholder="Ej: Tono D, Entra con solo de guitarra, puente x2"
                />
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showAddSongModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Agregar al Setlist') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
