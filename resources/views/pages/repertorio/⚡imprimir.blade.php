<?php

use App\Models\Cancion;
use App\Services\ChordTransposer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('components.layouts.blank')] #[Title('Vista de Impresión y Pantalla Completa')] class extends Component {
    public Cancion $cancion;

    #[Url]
    public int $semitonos = 0;

    #[Url]
    public int $tamanio = 16;

    public string $tonoActual = 'C';

    public function mount(Cancion $cancion): void
    {
        $this->cancion = $cancion->load('categoria');
        $this->actualizarTonoActual();
    }

    public function cambiarTono(int $delta): void
    {
        $this->semitonos += $delta;
        $this->actualizarTonoActual();
    }

    public function cambiarTamanio(int $delta): void
    {
        $this->tamanio = max(12, min(32, $this->tamanio + $delta));
    }

    private function actualizarTonoActual(): void
    {
        $tonoBase = strtoupper(trim($this->cancion->tono_original ?? 'C'));
        $this->tonoActual = $this->transponerAcorde($tonoBase, $this->semitonos);
    }

    public function transponerAcorde(string $acorde, int $semitonos): string
    {
        return ChordTransposer::transponerAcorde($acorde, $semitonos);
    }

    public function isAcordeToken(string $token): bool
    {
        return ChordTransposer::isAcordeToken($token);
    }

    public function isLineaDeAcordes(string $linea): bool
    {
        return ChordTransposer::isLineaDeAcordes($linea);
    }

    public function renderLetraConAcordes($texto)
    {
        if (! $texto) {
            return '';
        }

        $lineas = explode("\n", $texto);
        $style = 'font-size: ' . $this->tamanio . 'px;';
        $html = '<div style="' . $style . '" class="font-mono leading-relaxed tracking-wide space-y-1">';

        foreach ($lineas as $lineaOriginal) {
            $linea = rtrim($lineaOriginal);

            if (empty(trim($linea))) {
                $html .= '<div class="h-3"></div>';
                continue;
            }

            // Encabezados de sección tipo [Intro], [Primera Parte], [Coro], etc.
            if (preg_match('/^\s*(\[[^\]]+\])\s*(.*)$/', $linea, $m)) {
                $header = htmlspecialchars($m[1]);
                $resto = trim($m[2]);

                $html .= '<div class="pt-3 pb-1">';
                $html .= '<span class="font-bold text-zinc-500 dark:text-zinc-400 text-xs sm:text-sm tracking-wider uppercase">' . $header . '</span>';

                if (! empty($resto)) {
                    $html .= ' ';
                    $tokens = preg_split('/(\s+)/', $resto, -1, PREG_SPLIT_DELIM_CAPTURE);
                    foreach ($tokens as $token) {
                        if (trim($token) === '') {
                            $html .= $token;
                        } else {
                            $acorde = htmlspecialchars($token);
                            if ($this->semitonos !== 0 && $this->isAcordeToken($acorde)) {
                                $acorde = $this->transponerAcorde($acorde, $this->semitonos);
                            }
                            $html .= '<span class="font-bold text-[#1ed760]">' . $acorde . '</span>';
                        }
                    }
                }

                $html .= '</div>';
                continue;
            }

            // Líneas de solo acordes (estilo Cifra Club sobre la letra)
            if ($this->isLineaDeAcordes($linea)) {
                $html .= '<div class="font-bold text-[#1ed760] whitespace-pre leading-none pt-2 pb-0.5">';

                $tokens = preg_split('/(\s+)/', $linea, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach ($tokens as $token) {
                    if (trim($token) === '') {
                        $html .= $token;
                    } else {
                        $acorde = htmlspecialchars($token);
                        if ($this->semitonos !== 0 && $this->isAcordeToken($acorde)) {
                            $acorde = $this->transponerAcorde($acorde, $this->semitonos);
                        }
                        $html .= '<span>' . $acorde . '</span>';
                    }
                }

                $html .= '</div>';
                continue;
            }

            // Línea normal de letra
            $html .= '<div class="text-zinc-900 dark:text-zinc-100 whitespace-pre leading-relaxed py-0.5">' . htmlspecialchars($linea) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }
};
?>

<div class="min-h-screen bg-white dark:bg-[#121214] text-zinc-900 dark:text-zinc-100 font-mono antialiased selection:bg-[#1ed760] selection:text-black">
    <!-- Barra Flotante de Herramientas (Oculta en Impresión) -->
    <header class="print:hidden sticky top-0 z-50 bg-white/95 dark:bg-[#18181b]/95 backdrop-blur border-b border-zinc-200 dark:border-zinc-800 p-4 shadow-md">
        <div class="max-w-5xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="font-sans font-extrabold text-lg text-zinc-900 dark:text-white">
                    {{ $cancion->titulo }}
                </h1>
                @if(!empty($cancion->artista))
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 font-normal">· {{ $cancion->artista }}</span>
                @endif
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#1ed760]/15 text-[#1ed760] font-mono font-bold border border-[#1ed760]/30">
                    {{ $tonoActual }}
                </span>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <!-- Tonalidad -->
                <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-900 p-1 rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <button wire:click="cambiarTono(-1)" class="h-8 px-2 font-bold text-xs hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-300 transition-colors">
                        Tono -
                    </button>
                    <span class="text-xs font-bold text-[#1ed760] px-2 min-w-[2.5rem] text-center font-mono">{{ $tonoActual }}</span>
                    <button wire:click="cambiarTono(1)" class="h-8 px-2 font-bold text-xs hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-300 transition-colors">
                        Tono +
                    </button>
                </div>

                <!-- Tamaño de letra -->
                <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-900 p-1 rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <button wire:click="cambiarTamanio(-2)" class="h-8 px-2 font-bold text-xs hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-300 transition-colors">
                        A-
                    </button>
                    <span class="text-xs text-zinc-400 px-1 font-mono">{{ $tamanio }}px</span>
                    <button wire:click="cambiarTamanio(2)" class="h-8 px-2 font-bold text-xs hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-300 transition-colors">
                        A+
                    </button>
                </div>

                <!-- Botón Imprimir -->
                <button onclick="window.print()" class="h-9 px-4 rounded-lg bg-[#1ed760] hover:bg-[#1db954] text-black font-sans font-bold text-xs flex items-center gap-2 shadow-sm transition">
                    <flux:icon.printer class="size-4" />
                    <span>Imprimir / Guardar PDF</span>
                </button>

                <!-- Botón Cerrar -->
                <button onclick="window.close()" class="h-9 px-3 rounded-lg bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-sans font-medium text-xs transition">
                    Cerrar
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido Imprimible y de Pantalla Completa -->
    <main class="max-w-4xl mx-auto p-6 md:p-12">
        <!-- Encabezado de página visible SOLO al imprimir/guardar PDF (oculto en pantalla completa interactiva) -->
        <div class="hidden print:flex border-b border-zinc-200 dark:border-zinc-800 pb-6 mb-8 items-baseline justify-between">
            <div>
                <h1 class="font-sans text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                    {{ $cancion->titulo }}
                </h1>
                <p class="font-sans text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Artista: <strong class="text-zinc-800 dark:text-zinc-200">{{ $cancion->artista ?? 'Desconocido' }}</strong>
                </p>
            </div>

            <div class="text-right font-sans">
                <span class="text-xs text-zinc-400 uppercase tracking-wider block">Tonalidad</span>
                <span class="text-2xl font-bold text-[#1ed760]">{{ $tonoActual }}</span>
            </div>
        </div>

        <!-- Renderizado de la Canción con Acordes -->
        <div class="select-text">
            {!! $this->renderLetraConAcordes($cancion->letra) !!}
        </div>
    </main>
</div>
