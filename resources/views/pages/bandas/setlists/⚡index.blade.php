<?php

use App\Models\Banda;
use App\Models\Setlist;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;

    public bool $showCreateModal = false;
    public string $nombre = '';
    public string $descripcion = '';
    public string $fecha = '';
    public string $tipo = 'culto';

    public function mount(Banda $banda): void
    {
        $this->banda = $banda;
        session()->put('active_banda_id', $this->banda->id);
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->nombre = '';
        $this->descripcion = '';
        $this->fecha = now()->format('Y-m-d');
        $this->tipo = 'culto';
        $this->showCreateModal = true;
    }

    public function createSetlist(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'string', 'in:culto,ensayo,presentacion,otro'],
        ]);

        $setlist = Setlist::create([
            'banda_id' => $this->banda->id,
            'user_id' => auth()->id(),
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha' => $validated['fecha'],
            'tipo' => $validated['tipo'],
        ]);

        session()->flash('status', 'Setlist creado correctamente. Agrega tus canciones.');
        $this->showCreateModal = false;

        $this->redirectRoute('bandas.setlists.show', [
            'banda' => $this->banda->slug,
            'setlist' => $setlist->id,
        ], navigate: true);
    }

    public function render(): mixed
    {
        $setlists = $this->banda->setlists()
            ->withCount('canciones')
            ->with([
                'creador',
                'canciones' => function ($q) {
                    $q->orderBy('cancion_setlist.orden', 'asc');
                },
            ])
            ->orderBy('fecha', 'desc')
            ->paginate(12);

        return view('pages.bandas.setlists.⚡index', [
            'setlists' => $setlists,
        ])->title('Setlists - ' . $this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Setlists de ') . $this->banda->nombre }}</flux:heading>
            <flux:text variant="subtle">{{ __('Organiza las listas de canciones para ensayos, cultos y presentaciones en vivo.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nuevo Setlist') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Grilla de Setlists estilo Pinterest (Masonry) -->
    <div class="columns-1 md:columns-2 lg:columns-3 gap-6 [column-fill:_balance]">
        @forelse ($setlists as $setlist)
            <div wire:key="setlist-card-{{ $setlist->id }}" class="break-inside-avoid mb-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700/80 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-amber-500/50 dark:hover:border-amber-400/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <flux:badge color="{{ match($setlist->tipo) { 'culto' => 'purple', 'ensayo' => 'amber', 'presentacion' => 'sky', default => 'zinc' } }}" size="sm">
                            {{ Str::headline($setlist->tipo) }}
                        </flux:badge>

                        <flux:text variant="subtle" class="text-xs font-semibold">
                            {{ $setlist->fecha->format('d/m/Y') }}
                        </flux:text>
                    </div>

                    <flux:heading size="lg" level="2" class="leading-snug">
                        {{ $setlist->nombre }}
                    </flux:heading>

                    @if ($setlist->descripcion)
                        <flux:text variant="subtle" class="text-sm line-clamp-2">
                            {{ $setlist->descripcion }}
                        </flux:text>
                    @endif
                </div>

                <!-- Listita Rápida de Canciones del Setlist -->
                @if ($setlist->canciones->isNotEmpty())
                    <div class="space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 font-semibold">
                            <span>{{ __('Repertorio') }}</span>
                            <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-300">
                                {{ $setlist->canciones_count }} {{ Str::plural('canción', $setlist->canciones_count) }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($setlist->canciones as $idx => $cancion)
                                @php
                                    $tono = $cancion->pivot->tono ?: $cancion->tono_original;
                                @endphp
                                <div class="flex items-center justify-between gap-2 py-1 px-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100/80 dark:border-zinc-800/80 text-xs hover:bg-zinc-100/80 dark:hover:bg-zinc-800 transition">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-[10px]">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate">
                                            {{ $cancion->titulo }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if ($cancion->pivot->proposito)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                                                {{ $cancion->pivot->proposito }}
                                            </span>
                                        @endif
                                        @if ($tono)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">
                                                {{ $tono }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="py-3 px-3 bg-zinc-50/60 dark:bg-zinc-800/30 rounded-xl text-center border border-dashed border-zinc-200 dark:border-zinc-700/60">
                        <p class="text-xs text-zinc-400 italic">{{ __('Sin canciones aún') }}</p>
                    </div>
                @endif

                <div class="pt-2">
                    <flux:button variant="primary" class="w-full" icon="queue-list" :href="route('bandas.setlists.show', [$banda->slug, $setlist->id])" wire:navigate>
                        {{ __('Abrir Setlist') }}
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl space-y-4">
                <flux:icon.queue-list class="size-12 mx-auto text-zinc-400" />
                <flux:heading size="lg">{{ __('No hay setlists creados aún') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Crea el primer setlist para organizar las canciones de tu próxima reunión.') }}</flux:text>
                <div>
                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        {{ __('Crear Mi Primer Setlist') }}
                    </flux:button>
                </div>
            </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $setlists->links() }}
    </div>

    <!-- Modal Crear Setlist -->
    <flux:modal wire:model="showCreateModal" name="create-setlist-modal" class="max-w-md">
        <form wire:submit="createSetlist" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Nuevo Setlist') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Ingresa los datos del evento o presentación.') }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="nombre" label="Nombre del Setlist" placeholder="Ej: Culto Dominical, Ensayo General" required />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="fecha" type="date" label="Fecha del Evento" required />

                    <flux:select wire:model="tipo" label="Tipo">
                        <flux:select.option value="culto">{{ __('Culto / Servicio') }}</flux:select.option>
                        <flux:select.option value="ensayo">{{ __('Ensayo') }}</flux:select.option>
                        <flux:select.option value="presentacion">{{ __('Presentación / Concierto') }}</flux:select.option>
                        <flux:select.option value="otro">{{ __('Otro') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="descripcion" label="Descripción u Observaciones" rows="3" placeholder="Ej: Orden especial, entrada con batería..." />
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showCreateModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Crear y Agregar Canciones') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
