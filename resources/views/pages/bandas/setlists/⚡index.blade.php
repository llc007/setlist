<?php

use App\Models\Banda;
use App\Models\Setlist;
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
            ->with('creador')
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

    <!-- Grilla de Setlists -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($setlists as $setlist)
            <div wire:key="setlist-card-{{ $setlist->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-500 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:badge color="{{ match($setlist->tipo) { 'culto' => 'purple', 'ensayo' => 'amber', 'presentacion' => 'sky', default => 'zinc' } }}" size="sm">
                            {{ Str::headline($setlist->tipo) }}
                        </flux:badge>

                        <flux:text variant="subtle" class="text-xs font-semibold">
                            {{ $setlist->fecha->format('d/m/Y') }}
                        </flux:text>
                    </div>

                    <flux:heading size="lg" level="2">{{ $setlist->nombre }}</flux:heading>

                    @if ($setlist->descripcion)
                        <flux:text variant="subtle" class="text-sm line-clamp-2">
                            {{ $setlist->descripcion }}
                        </flux:text>
                    @endif
                </div>

                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between text-xs py-2 px-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <span class="text-zinc-500">{{ __('Canciones:') }}</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $setlist->canciones_count }}</span>
                    </div>

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
