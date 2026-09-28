<?php

use App\Models\Cancion;
use App\Models\Setlist;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.blank')] class extends Component {
    public Setlist $setlist;
    public ?int $viewingCancionId = null;

    public function mount(Setlist $setlist): void
    {
        $this->setlist = $setlist->load(['banda', 'canciones.categoria']);
    }

    public function toggleCancion(int $cancionId): void
    {
        $this->viewingCancionId = ($this->viewingCancionId === $cancionId) ? null : $cancionId;
    }
};
?>

<div class="min-h-screen bg-zinc-50 dark:bg-[#121214] text-zinc-900 dark:text-zinc-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Barra Superior / Marca -->
        <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <div class="h-9 w-9 rounded-xl bg-amber-500 flex items-center justify-center text-white font-black text-lg shadow-sm">
                    S
                </div>
                <div>
                    <span class="font-bold text-base text-zinc-900 dark:text-white tracking-tight">Setlist</span>
                    <span class="text-xs text-zinc-400 block -mt-1">Vista Compartida</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    onclick="window.print()"
                    class="h-8 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-700 transition flex items-center gap-1.5"
                >
                    <flux:icon.printer class="size-3.5" />
                    <span>Imprimir</span>
                </button>

                @auth
                    <a
                        href="{{ route('bandas.setlists.show', [$setlist->banda->slug, $setlist->id]) }}"
                        class="h-8 px-3 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs font-semibold hover:opacity-90 transition flex items-center gap-1"
                    >
                        <span>Administrar</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Encabezado del Setlist -->
        <div class="bg-white dark:bg-[#18181b] border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                    {{ $setlist->banda->nombre }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                    {{ Str::headline($setlist->tipo) }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                {{ $setlist->nombre }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                @if ($setlist->fecha)
                    <div class="flex items-center gap-1.5 font-medium">
                        <span>📅 {{ $setlist->fecha->format('d/m/Y') }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <span>🎼 {{ $setlist->canciones->count() }} canciones</span>
                </div>
            </div>

            @if ($setlist->descripcion)
                <p class="text-sm text-zinc-600 dark:text-zinc-300 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                    {{ $setlist->descripcion }}
                </p>
            @endif
        </div>

        <!-- Lista de Canciones -->
        <div class="space-y-3">
            @forelse ($setlist->canciones as $idx => $cancion)
                @php
                    $tonoCancion = $cancion->pivot->tono ?: $cancion->tono_original;
                @endphp
                <div
                    wire:key="cancion-public-{{ $cancion->id }}"
                    class="bg-white dark:bg-[#18181b] border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 shadow-sm transition hover:border-zinc-300 dark:hover:border-zinc-700"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white font-bold text-sm mt-0.5 shadow-sm">
                                {{ $idx + 1 }}
                            </span>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-bold text-base sm:text-lg text-zinc-900 dark:text-zinc-100">
                                        {{ $cancion->titulo }}
                                    </h2>

                                    @if ($cancion->pivot->proposito)
                                        <flux:badge color="indigo" size="sm" icon="flag">
                                            {{ $cancion->pivot->proposito }}
                                        </flux:badge>
                                    @endif

                                    @if ($tonoCancion)
                                        <flux:badge color="amber" size="sm" icon="musical-note">
                                            Tono: {{ $tonoCancion }}
                                            @if ($cancion->tono_original && $cancion->pivot->tono && $cancion->tono_original !== $cancion->pivot->tono)
                                                <span class="text-zinc-400 font-normal ml-1">({{ $cancion->tono_original }})</span>
                                            @endif
                                        </flux:badge>
                                    @endif

                                    @if ($cancion->categoria)
                                        <flux:badge color="zinc" size="sm">
                                            {{ $cancion->categoria->nombre }}
                                        </flux:badge>
                                    @endif
                                </div>

                                <p class="text-xs text-zinc-500">{{ $cancion->artista ?? 'Sin artista especificado' }}</p>

                                @if ($cancion->pivot->observacion || $cancion->pivot->nota)
                                    <div class="mt-2 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-lg px-3 py-1.5 inline-flex items-center gap-2 font-medium">
                                        <span>📝 {{ $cancion->pivot->observacion ?: $cancion->pivot->nota }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Botón para ver acordes / Pantalla completa -->
                        <div class="shrink-0 flex items-center gap-1.5">
                            <a
                                href="{{ route('canciones.imprimir', ['cancion' => $cancion->id]) }}"
                                target="_blank"
                                class="h-9 px-3 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition flex items-center gap-1.5"
                                title="Ver letra y acordes en pantalla completa"
                            >
                                <flux:icon.musical-note class="size-4 text-amber-600 dark:text-amber-400" />
                                <span class="hidden sm:inline">Ver Acordes</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-[#18181b] border border-zinc-200 dark:border-zinc-800 rounded-2xl p-12 text-center text-zinc-500">
                    <flux:icon.musical-note class="size-12 mx-auto text-zinc-400 mb-3" />
                    <p class="font-medium text-base">Este setlist no tiene canciones agregadas aún.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
