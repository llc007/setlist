<?php

use App\Models\Banda;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Str;

new #[Layout('components.layouts.app.sidebar')] #[Title('Mis Bandas')] class extends Component {
    public bool $showCreateModal = false;
    public string $nombre = '';
    public string $descripcion = '';
    public string $tipo_ambito = 'cristiano';

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->nombre = '';
        $this->descripcion = '';
        $this->tipo_ambito = 'cristiano';
        $this->showCreateModal = true;
    }

    public function createBanda(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'tipo_ambito' => ['required', 'string', 'in:cristiano,secular,mixto'],
        ]);

        $banda = Banda::create([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
            'tipo_ambito' => $validated['tipo_ambito'],
        ]);

        // Asignar al creador como Administrador de Banda
        $banda->miembros()->attach(auth()->id(), [
            'rol' => 'Administrador de Banda',
        ]);

        session()->put('active_banda_id', $banda->id);
        session()->flash('status', 'Banda creada exitosamente.');

        $this->showCreateModal = false;
        $this->redirectRoute('bandas.show', ['banda' => $banda->slug], navigate: true);
    }

    public function selectBanda(int $bandaId, string $slug): void
    {
        session()->put('active_banda_id', $bandaId);
        $this->redirectRoute('bandas.show', ['banda' => $slug], navigate: true);
    }

    public function render(): mixed
    {
        $user = auth()->user();

        $bandas = $user->hasRole('SuperAdministrador')
            ? Banda::withCount(['miembros', 'repertorio', 'setlists'])->latest()->get()
            : $user->bandas()->withCount(['miembros', 'repertorio', 'setlists'])->latest()->get();

        return view('pages.bandas.⚡index', [
            'bandas' => $bandas,
        ]);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Mis Bandas y Grupos') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Selecciona una banda para acceder a su repertorio exclusivo, setlists y gestión de miembros.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Crear Nueva Banda') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Grilla de Bandas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($bandas as $banda)
            @php
                $isActive = session('active_banda_id') == $banda->id;
            @endphp
            <div wire:key="banda-card-{{ $banda->id }}" class="bg-white dark:bg-zinc-900 border {{ $isActive ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-zinc-200 dark:border-zinc-700' }} rounded-xl p-6 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-500 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                                <flux:icon.user-group variant="outline" class="size-6" />
                            </span>
                            <div>
                                <flux:heading size="lg" level="2">{{ $banda->nombre }}</flux:heading>
                                @if ($isActive)
                                    <flux:badge color="purple" size="sm" class="mt-0.5">{{ __('Activa') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($banda->descripcion)
                        <flux:text variant="subtle" class="text-sm line-clamp-2">
                            {{ $banda->descripcion }}
                        </flux:text>
                    @endif
                </div>

                <div class="space-y-4 pt-2">
                    <div class="grid grid-cols-3 gap-2 py-3 px-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-center text-xs">
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $banda->repertorio_count ?? 0 }}</span>
                            <span class="text-zinc-500">{{ __('Canciones') }}</span>
                        </div>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $banda->setlists_count ?? 0 }}</span>
                            <span class="text-zinc-500">{{ __('Setlists') }}</span>
                        </div>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $banda->miembros_count ?? 0 }}</span>
                            <span class="text-zinc-500">{{ __('Miembros') }}</span>
                        </div>
                    </div>

                    <flux:button variant="primary" class="w-full" wire:click="selectBanda({{ $banda->id }}, '{{ $banda->slug }}')">
                        {{ __('Ingresar a la Banda') }}
                    </flux:button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl space-y-4">
                <flux:icon.user-group class="size-12 mx-auto text-zinc-400" />
                <flux:heading size="lg">{{ __('No perteneces a ninguna banda aún') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Crea tu primera banda para gestionar canciones, repertorios y armar tu primer setlist.') }}</flux:text>
                <div>
                    <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                        {{ __('Crear Mi Primera Banda') }}
                    </flux:button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal Crear Banda -->
    <flux:modal wire:model="showCreateModal" name="create-banda-modal" class="max-w-md">
        <form wire:submit="createBanda" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Crear Nueva Banda') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Ingresa el nombre y descripción para dar de alta la banda.') }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:input
                    wire:model="nombre"
                    label="Nombre de la Banda o Grupo"
                    placeholder="Ej: Banda Alabanza Central, Grupo Joven"
                    required
                />

                <flux:textarea
                    wire:model="descripcion"
                    label="Descripción (Opcional)"
                    placeholder="Ej: Grupo de alabanza para reuniones de fin de semana..."
                    rows="3"
                />

                <flux:select wire:model="tipo_ambito" label="Tipo / Ámbito del Grupo" required>
                    <flux:select.option value="cristiano">⛪ {{ __('Ministerio / Iglesia / Alabanza (Cristiano)') }}</flux:select.option>
                    <flux:select.option value="secular">🎸 {{ __('Banda Comercial / Eventos / Bares (General)') }}</flux:select.option>
                    <flux:select.option value="mixto">🎶 {{ __('Mixto / Versátil (Ambos repertorios)') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showCreateModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Crear Banda') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
