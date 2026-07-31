<?php

use App\Models\Banda;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;

    public function mount(Banda $banda): void
    {
        $this->banda = $banda->loadCount(['miembros', 'repertorio', 'setlists']);
        session()->put('active_banda_id', $this->banda->id);
    }

    public function render(): mixed
    {
        $proximosSetlists = $this->banda->setlists()
            ->withCount('canciones')
            ->orderBy('fecha', 'asc')
            ->where('fecha', '>=', now()->startOfDay())
            ->take(5)
            ->get();

        $cancionesRecientes = $this->banda->repertorio()
            ->with('categoria')
            ->latest('banda_cancion.created_at')
            ->take(5)
            ->get();

        return view('pages.bandas.⚡show', [
            'proximosSetlists' => $proximosSetlists,
            'cancionesRecientes' => $cancionesRecientes,
        ])->title($this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado de la Banda -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-2xl shadow-md">
                {{ Str::upper(Str::substr($banda->nombre, 0, 2)) }}
            </span>
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1">{{ $banda->nombre }}</flux:heading>
                    <flux:badge color="purple" size="sm">{{ __('Banda Activa') }}</flux:badge>
                </div>
                @if ($banda->descripcion)
                    <flux:text variant="subtle" class="mt-1">{{ $banda->descripcion }}</flux:text>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button variant="primary" icon="plus" :href="route('bandas.setlists.index', $banda->slug)" wire:navigate>
                {{ __('Nuevo Setlist') }}
            </flux:button>
            <flux:button variant="outline" icon="musical-note" :href="route('bandas.repertorio.index', $banda->slug)" wire:navigate>
                {{ __('Repertorio') }}
            </flux:button>
            <flux:button variant="outline" icon="users" :href="route('bandas.miembros', $banda->slug)" wire:navigate>
                {{ __('Miembros') }}
            </flux:button>
        </div>
    </div>

    <!-- Métrica de Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <flux:text variant="subtle" class="text-xs font-bold uppercase tracking-wider">{{ __('Canciones en Repertorio') }}</flux:text>
                <flux:icon.musical-note class="size-5 text-indigo-500" />
            </div>
            <div class="text-3xl font-black text-zinc-900 dark:text-zinc-100">
                {{ $banda->repertorio_count }}
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <flux:text variant="subtle" class="text-xs font-bold uppercase tracking-wider">{{ __('Setlists Creados') }}</flux:text>
                <flux:icon.queue-list class="size-5 text-purple-500" />
            </div>
            <div class="text-3xl font-black text-zinc-900 dark:text-zinc-100">
                {{ $banda->setlists_count }}
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <flux:text variant="subtle" class="text-xs font-bold uppercase tracking-wider">{{ __('Integrantes de la Banda') }}</flux:text>
                <flux:icon.users class="size-5 text-sky-500" />
            </div>
            <div class="text-3xl font-black text-zinc-900 dark:text-zinc-100">
                {{ $banda->miembros_count }}
            </div>
        </div>
    </div>

    <!-- Sección Principal: Próximos Servicios y Canciones -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Próximos Setlists -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Próximos Setlists / Ensayo') }}</flux:heading>
                <flux:button variant="ghost" size="sm" icon-trailing="chevron-right" :href="route('bandas.setlists.index', $banda->slug)" wire:navigate>
                    {{ __('Ver Todos') }}
                </flux:button>
            </div>

            <div class="space-y-3">
                @forelse ($proximosSetlists as $setlist)
                    <div wire:key="setlist-dash-{{ $setlist->id }}" class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col items-center justify-center h-12 w-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                <span class="text-xs font-bold uppercase">{{ $setlist->fecha->format('M') }}</span>
                                <span class="text-lg font-black leading-tight">{{ $setlist->fecha->format('d') }}</span>
                            </div>

                            <div>
                                <flux:heading size="base">{{ $setlist->nombre }}</flux:heading>
                                <flux:text variant="subtle" class="text-xs">
                                    {{ Str::headline($setlist->tipo) }} • {{ $setlist->canciones_count }} {{ __('canciones') }}
                                </flux:text>
                            </div>
                        </div>

                        <flux:button variant="outline" size="sm" icon="eye" :href="route('bandas.setlists.show', [$banda->slug, $setlist->id])" wire:navigate>
                            {{ __('Ver Setlist') }}
                        </flux:button>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-500">
                        {{ __('No hay setlists programados para los próximos días.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Canciones Agregadas Recientemente -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Repertorio Reciente') }}</flux:heading>
                <flux:button variant="ghost" size="sm" icon-trailing="chevron-right" :href="route('bandas.repertorio.index', $banda->slug)" wire:navigate>
                    {{ __('Ver Repertorio') }}
                </flux:button>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm space-y-3">
                @forelse ($cancionesRecientes as $cancion)
                    <div wire:key="cancion-dash-{{ $cancion->id }}" class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                        <div>
                            <div class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">{{ $cancion->titulo }}</div>
                            <div class="text-xs text-zinc-500">{{ $cancion->artista ?? 'Desconocido' }} • Tono: {{ $cancion->tono_original ?? 'N/A' }}</div>
                        </div>

                        <flux:badge size="sm" color="{{ $cancion->es_publica ? 'sky' : 'amber' }}">
                            {{ $cancion->es_publica ? 'Pública' : 'Privada' }}
                        </flux:badge>
                    </div>
                @empty
                    <div class="text-center py-6 text-zinc-500 text-sm">
                        {{ __('Sin canciones en el repertorio aún.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
