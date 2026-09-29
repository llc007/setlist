<?php

use App\Models\Cancion;
use App\Services\ChordTransposer;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public Cancion $cancion;

    public $transposicion = 0;
    public $tonoActual;
    public $letra;

    public function getPdfUrlProperty()
    {
        if ($this->cancion->pdf_path) {
            if (filter_var($this->cancion->pdf_path, FILTER_VALIDATE_URL)) {
                return $this->formatGoogleDriveUrl($this->cancion->pdf_path);
            }
            return \Illuminate\Support\Facades\Storage::url($this->cancion->pdf_path);
        }

        $recursoPdf = $this->cancion->recursos->where('tipo', 'pdf')->first();
        if ($recursoPdf) {
            return $this->formatGoogleDriveUrl($recursoPdf->url);
        }

        return null;
    }

    private function formatGoogleDriveUrl($url)
    {
        if (str_contains($url, 'drive.google.com') && str_contains($url, '/view')) {
            return str_replace('/view', '/preview', $url);
        }
        return $url;
    }

    private array $escalas = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
    private array $bemoles = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    public bool $modoEdicion = false;

    public function mount(Cancion $cancion)
    {
        $this->cancion = $cancion->load('recursos', 'categoria');
        $this->tonoActual = $cancion->tono_original ?? 'C';
        $this->letra = $cancion->letra;
        $this->actualizarTonoActual();
    }

    #[On('cancion-actualizada')]
    public function cancionActualizada(): void
    {
        $this->cancion->refresh();
        $this->cancion->load('recursos', 'categoria');
        $this->tonoActual = $this->cancion->tono_original ?? 'C';
        $this->letra = $this->cancion->letra;
        $this->transposicion = 0;
        $this->actualizarTonoActual();
    }

    public function guardarLetra(?string $nuevaLetra = null): void
    {
        if ($nuevaLetra !== null) {
            $this->letra = $nuevaLetra;
        }

        $this->cancion->update(['letra' => $this->letra]);
        $this->modoEdicion = false;
        $this->dispatch('modal-close', name: 'modal-interactivo');
        $this->dispatch('cancion-actualizada');
    }

    public function cambiarTono($cantidad)
    {
        $this->transposicion += $cantidad;
        $this->actualizarTonoActual();
    }

    public function guardarTonoTranspuesto(): void
    {
        if ($this->transposicion === 0) {
            return;
        }

        $nuevaLetra = ChordTransposer::transponerTexto($this->cancion->letra ?? '', $this->transposicion);
        $nuevoTono = $this->tonoActual;

        $this->cancion->update([
            'tono_original' => $nuevoTono,
            'letra' => $nuevaLetra,
        ]);

        $this->letra = $nuevaLetra;
        $this->transposicion = 0;
        $this->actualizarTonoActual();

        $this->dispatch('cancion-actualizada');
    }

    public function restablecerTono(): void
    {
        $this->transposicion = 0;
        $this->actualizarTonoActual();
    }

    private function actualizarTonoActual(): void
    {
        $tonoBase = strtoupper(trim($this->cancion->tono_original ?? 'C'));
        $this->tonoActual = $this->transponerAcorde($tonoBase, $this->transposicion);
    }

    public function transponerAcorde(string $acorde, int $semitonos): string
    {
        return ChordTransposer::transponerAcorde($acorde, $semitonos);
    }

    public int $tamanioLetra = 16;

    public function cambiarTamanioLetra(int $delta): void
    {
        $this->tamanioLetra = max(12, min(28, $this->tamanioLetra + $delta));
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
        $style = 'font-size: ' . $this->tamanioLetra . 'px;';
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
                            if ($this->transposicion !== 0 && $this->isAcordeToken($acorde)) {
                                $acorde = $this->transponerAcorde($acorde, $this->transposicion);
                            }
                            $html .= '<span class="font-bold text-amber-600 dark:text-amber-400">' . $acorde . '</span>';
                        }
                    }
                }

                $html .= '</div>';
                continue;
            }

            // Líneas de solo acordes (estilo Cifra Club sobre la letra)
            if ($this->isLineaDeAcordes($linea)) {
                $html .= '<div class="font-bold text-amber-600 dark:text-amber-400 whitespace-pre leading-none pt-2 pb-0.5">';

                $tokens = preg_split('/(\s+)/', $linea, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach ($tokens as $token) {
                    if (trim($token) === '') {
                        $html .= $token;
                    } else {
                        $acorde = htmlspecialchars($token);
                        if ($this->transposicion !== 0 && $this->isAcordeToken($acorde)) {
                            $acorde = $this->transponerAcorde($acorde, $this->transposicion);
                        }
                        $html .= '<span>' . $acorde . '</span>';
                    }
                }

                $html .= '</div>';
                continue;
            }

            // Líneas con corchetes integrados [C] Letra
            if (str_contains($linea, '[')) {
                $html .= '<div class="flex flex-wrap items-end gap-y-3 pt-1 pb-1">';
                $partes = preg_split('/(\[[^\]]+\])/', $linea, -1, PREG_SPLIT_DELIM_CAPTURE);
                $acordeActual = '';

                foreach ($partes as $parte) {
                    if (preg_match('/\[([^\]]+)\]/', $parte, $coincidencias)) {
                        $acordeActual = htmlspecialchars($coincidencias[1]);
                        if ($this->transposicion !== 0) {
                            $acordeActual = $this->transponerAcorde($acordeActual, $this->transposicion);
                        }
                    } else {
                        $textoSegmento = htmlspecialchars($parte);
                        $html .= '<div class="inline-flex flex-col justify-end text-left pr-1.5">';
                        $html .= '<span class="font-bold text-amber-600 dark:text-amber-400 text-xs sm:text-sm h-4 leading-none">' . $acordeActual . '</span>';
                        $html .= '<span class="text-zinc-900 dark:text-zinc-100 text-sm sm:text-base leading-tight">' . ($textoSegmento !== '' ? $textoSegmento : '&nbsp;') . '</span>';
                        $html .= '</div>';
                        $acordeActual = '';
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
}; ?>

<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 text-sm">
            <a class="text-slate-500 dark:text-[#9dabb9] hover:text-primary transition-colors font-medium"
                href="{{ route('repertorio') }}">Canciones</a>
            <span class="text-slate-400 dark:text-[#9dabb9] material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-slate-900 dark:text-white font-medium">{{ $cancion->titulo }}</span>
        </div>
        <div class="flex gap-2">
            <button
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm"
                type="button">
                <span class="material-symbols-outlined text-[18px]">ios_share</span>
                <span class="hidden sm:inline">Compartir</span>
            </button>
            <a href="{{ route('canciones.imprimir', ['cancion' => $cancion->id, 'semitonos' => $this->transposicion, 'tamanio' => $this->tamanioLetra]) }}"
                target="_blank"
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span class="hidden sm:inline">Imprimir / PDF</span>
            </a>
            <button wire:click="$set('modoEdicion', true)"
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition-colors shadow-lg shadow-amber-600/20"
                type="button">
                <span class="material-symbols-outlined text-[18px]">tune</span>
                <span>Editar Acordes y Letra</span>
            </button>
            <button wire:click="$dispatch('abrir-modal-edicion', { id: {{ $cancion->id }} })"
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm"
                type="button">
                <span class="material-symbols-outlined text-[18px]">settings</span>
                <span>Detalles</span>
            </button>
        </div>
    </div>

    <div class="flex flex-wrap justify-between items-end gap-6 pb-6 border-b border-gray-200 dark:border-[#283039]">
        <div class="flex flex-col gap-2">
            <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black tracking-tight">
                {{ $cancion->codigo ? $cancion->codigo . " - " . $cancion->titulo : $cancion->titulo }}
            </h1>
            <div class="flex items-center gap-2 text-slate-500 dark:text-[#9dabb9]">
                <span class="material-symbols-outlined text-[20px]">mic</span>
                <p class="text-lg font-normal">{{ $cancion->artista ?? 'Desconocido' }}</p>
            </div>
        </div>
        <a href="{{ route('canciones.imprimir', ['cancion' => $cancion->id, 'semitonos' => $this->transposicion, 'tamanio' => $this->tamanioLetra]) }}"
            target="_blank"
            class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-slate-900 dark:bg-white text-white dark:text-black text-sm font-bold hover:bg-slate-800 dark:hover:bg-gray-200 transition-colors shadow-md">
            <span class="material-symbols-outlined text-[20px] material-symbols-filled">slideshow</span>
            <span>Pantalla Completa / Impresión</span>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Tonalidad</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">{{ $tonoActual }}</p>
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Ámbito</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold capitalize">{{ $cancion->ambito ?? 'cristiano' }}</p>
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Categoría</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">{{ $cancion->categoria?->nombre ?? 'General' }}</p>
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Tipo</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">{{ $cancion->es_publica ? 'Pública' : 'Privada' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full min-h-[600px] mt-6">
        <div
            class="lg:col-span-7 xl:col-span-8 flex flex-col bg-white dark:bg-[#18181b] rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm dark:shadow-2xl">
            @if($modoEdicion)
                <div x-data="editorAcordes({ initialText: @js($cancion->letra ?? '') })" class="flex flex-col h-full">
                    <!-- Barra superior del Editor -->
                    <div class="flex flex-wrap items-center justify-between p-3.5 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/90 dark:bg-[#18181b]/95 backdrop-blur-sm sticky top-0 z-20 gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center size-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <span class="material-symbols-outlined text-[20px]">tune</span>
                            </span>
                            <div>
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm flex items-center gap-2">
                                    <span>Editor Interactivo de Acordes</span>
                                    <span class="text-[11px] font-normal px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Arrastrar y Soltar</span>
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Toggle Visual / Texto Plano -->
                            <div class="flex bg-zinc-200 dark:bg-zinc-800 p-0.5 rounded-lg text-xs font-semibold">
                                <button type="button" @click="tab = 'visual'"
                                    :class="tab === 'visual' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                                    class="px-2.5 py-1 rounded-md transition-all">
                                    Visual
                                </button>
                                <button type="button" @click="tab = 'raw'"
                                    :class="tab === 'raw' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                                    class="px-2.5 py-1 rounded-md transition-all">
                                    Texto
                                </button>
                            </div>

                            <button type="button" wire:click="$set('modoEdicion', false)"
                                class="h-8 px-3 rounded-lg text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                                Cancelar
                            </button>

                            <button type="button" @click="saveChanges($wire)"
                                class="h-8 px-3.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white flex items-center gap-1.5 shadow-sm shadow-amber-600/30 transition">
                                <span class="material-symbols-outlined text-[16px]">save</span>
                                <span>Guardar Cambios</span>
                            </button>
                        </div>
                    </div>

                    <!-- Paleta rápida de acordes arrastrables -->
                    <div x-show="tab === 'visual'" class="px-4 py-2 bg-amber-500/[0.04] dark:bg-amber-950/20 border-b border-amber-500/10 flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1 shrink-0">
                            <span class="material-symbols-outlined text-[15px]">drag_indicator</span>
                            <span>Acordes rápidos:</span>
                        </span>
                        <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto py-0.5">
                            <template x-for="ch in paletteChords" :key="ch">
                                <div draggable="true"
                                    @dragstart="startDragPalette(ch, $event)"
                                    @click="selectedChord = ch"
                                    :class="selectedChord === ch ? 'ring-2 ring-amber-500 bg-amber-500 text-white dark:bg-amber-500' : 'bg-white dark:bg-zinc-800 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-800 hover:bg-amber-50 dark:hover:bg-amber-950/40'"
                                    class="cursor-grab active:cursor-grabbing select-none font-mono font-bold px-2 py-0.5 rounded shadow-2xs transition transform hover:scale-105"
                                    :title="'Arrastra ' + ch + ' a cualquier línea o selecciónalo'">
                                    <span x-text="ch"></span>
                                </div>
                            </template>
                        </div>
                        <div class="ml-auto flex items-center gap-1 shrink-0">
                            <input type="text" x-model="customChordInput" placeholder="Otro..." @keydown.enter.prevent="addCustomPaletteChord()" class="h-6 w-20 px-2 text-xs font-mono rounded bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 focus:outline-none focus:ring-1 ring-amber-500 text-zinc-800 dark:text-zinc-200">
                            <button type="button" @click="addCustomPaletteChord()" class="h-6 px-2 text-xs bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 rounded font-bold hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Añadir a la paleta">+</button>
                        </div>
                    </div>

                    <!-- Contenedor del Editor Visual -->
                    <div x-show="tab === 'visual'" class="p-4 md:p-6 overflow-y-auto max-h-[750px] font-mono select-text bg-white dark:bg-[#18181b] space-y-3">
                        <!-- Medidor oculto de ancho de carácter para alineación exacta -->
                        <span x-ref="measureSpan" class="font-mono text-sm sm:text-base invisible absolute -left-[9999px] whitespace-pre select-none pointer-events-none">01234567890123456789</span>

                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-2 flex items-center gap-2 bg-zinc-50 dark:bg-zinc-900/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <span class="material-symbols-outlined text-[16px] text-amber-500">lightbulb</span>
                            <span><b>Arrastra</b> acordes sobre la letra para sincronizarlos. Haz <b>clic</b> en la pista superior para colocar el acorde seleccionado (<span class="font-mono font-bold text-amber-600 dark:text-amber-400" x-text="selectedChord"></span>). Haz <b>clic en un acorde</b> para editarlo.</span>
                        </div>

                        <!-- Lista de líneas interactivas -->
                        <template x-for="(line, lineIdx) in lines" :key="line.id">
                            <div class="group relative py-1 hover:bg-zinc-50/80 dark:hover:bg-zinc-900/50 rounded-lg transition px-2">
                                <!-- Caso 1: Header de Sección -->
                                <template x-if="line.type === 'header'">
                                    <div class="flex items-center gap-2 pt-2 pb-1">
                                        <span class="material-symbols-outlined text-[18px] text-zinc-400">bookmark</span>
                                        <input type="text" x-model="line.text" class="font-bold text-zinc-600 dark:text-zinc-300 text-xs sm:text-sm tracking-wider uppercase bg-transparent border-0 border-b border-zinc-300 dark:border-zinc-700 focus:border-amber-500 focus:ring-0 p-0.5 outline-none w-48">
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                            <button type="button" @click="addLine(lineIdx)" class="text-xs px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200" title="Insertar línea debajo">+ Línea</button>
                                            <button type="button" @click="removeLine(lineIdx)" class="text-xs p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded" title="Eliminar sección">
                                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Caso 2: Línea en blanco -->
                                <template x-if="line.type === 'blank'">
                                    <div class="h-4 flex items-center justify-between group/blank">
                                        <div class="h-px bg-zinc-100 dark:bg-zinc-800/60 w-full"></div>
                                        <button type="button" @click="addLine(lineIdx)" class="opacity-0 group-hover/blank:opacity-100 text-[10px] text-zinc-400 hover:text-amber-500 whitespace-nowrap px-2">+ Añadir letra aquí</button>
                                    </div>
                                </template>

                                <!-- Caso 3: Línea con Letra y Acordes -->
                                <template x-if="line.type === 'lyric'">
                                    <div class="space-y-0.5">
                                        <!-- Pista de Acordes (Chord Track) -->
                                        <div class="relative h-7 w-full border-b border-dashed border-amber-500/20 bg-amber-500/[0.02] hover:bg-amber-500/[0.06] rounded-t transition cursor-crosshair"
                                            @dragover.prevent="onDragOver(lineIdx, $event)"
                                            @dragleave="onDragLeave(lineIdx, $event)"
                                            @drop.prevent="onDrop(lineIdx, $event)"
                                            @click="onTrackClick(lineIdx, $event)"
                                            title="Haz clic aquí para agregar un acorde">

                                            <!-- Indicador visual de posición al arrastrar -->
                                            <div x-show="dragOverLineIdx === lineIdx && dragOverCol !== null"
                                                :style="'left: ' + (dragOverCol * charWidth) + 'px;'"
                                                class="absolute top-0 bottom-0 w-0.5 bg-amber-500 shadow-sm pointer-events-none z-20"></div>

                                            <!-- Chips de Acordes colocados -->
                                            <template x-for="(ch, chIdx) in line.chords" :key="ch.id">
                                                <div data-chord-chip="true"
                                                    :style="'left: ' + (ch.col * charWidth) + 'px;'"
                                                    draggable="true"
                                                    @dragstart.stop="startDragChord(lineIdx, chIdx, $event)"
                                                    @click.stop="openEditChord(lineIdx, chIdx)"
                                                    class="absolute top-0.5 inline-flex items-center gap-1 font-mono font-bold text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/80 border border-amber-300 dark:border-amber-700/80 rounded px-1.5 py-0.5 shadow-2xs cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-amber-500 transition-all select-none z-10"
                                                    title="Haz clic para editar o arrastra para mover">
                                                    <span x-text="ch.chord"></span>
                                                    <button type="button" @click.stop="deleteChord(lineIdx, chIdx)" class="text-zinc-400 hover:text-red-500 text-[10px] leading-none ml-0.5" title="Eliminar acorde">&times;</button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Línea de Letra editable -->
                                        <div class="relative flex items-center justify-between gap-2">
                                            <input type="text"
                                                x-model="line.text"
                                                placeholder="Escribe la letra aquí..."
                                                class="w-full font-mono text-sm sm:text-base leading-relaxed tracking-wide bg-transparent border-0 border-b border-transparent focus:border-amber-500 focus:ring-0 text-zinc-900 dark:text-zinc-100 p-0 m-0 outline-none">

                                            <!-- Botones flotantes de acción de línea -->
                                            <div class="opacity-0 group-hover:opacity-100 transition flex items-center gap-1 shrink-0">
                                                <button type="button" @click="addLine(lineIdx)" class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Añadir línea debajo">
                                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                                </button>
                                                <button type="button" @click="addHeader(lineIdx, 'Coro')" class="p-1 rounded text-zinc-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 text-[11px] font-bold" title="Insertar sección debajo">
                                                    +Sección
                                                </button>
                                                <button type="button" @click="removeLine(lineIdx)" class="p-1 rounded text-zinc-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30" title="Eliminar línea">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Botones de añadir al final -->
                        <div class="pt-4 flex items-center gap-2 border-t border-zinc-100 dark:border-zinc-800/80">
                            <button type="button" @click="addLine(lines.length - 1)" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-amber-500 hover:text-amber-500 transition">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                <span>Añadir Línea al Final</span>
                            </button>
                            <button type="button" @click="addHeader(lines.length - 1, 'Nueva Sección')" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-amber-500 hover:text-amber-500 transition">
                                <span class="material-symbols-outlined text-[16px]">bookmark_add</span>
                                <span>Añadir Sección</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modo Texto Plano -->
                    <div x-show="tab === 'raw'" class="p-4 md:p-6 bg-white dark:bg-[#18181b] flex flex-col h-full">
                        <p class="text-xs text-zinc-500 mb-2">Edita el texto con acordes directamente. Al volver a la pestaña <b>Visual</b>, los cambios se sincronizarán automáticamente.</p>
                        <textarea x-model="rawText" class="w-full h-[600px] font-mono p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-sm leading-relaxed text-zinc-900 dark:text-zinc-100 outline-none focus:ring-2 ring-amber-500/30"></textarea>
                    </div>

                    <!-- Modal Flotante de Edición de Acorde -->
                    <div x-show="editingChord !== null"
                        x-cloak
                        @click.outside="saveEditChord()"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
                        @keydown.escape.window="editingChord = null"
                        @keydown.enter.window="saveEditChord()">
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-2xl p-5 w-full max-w-sm space-y-4"
                            @click.stop="">
                            <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                <h4 class="font-bold text-zinc-900 dark:text-white text-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-amber-500 text-[18px]">music_note</span>
                                    Editar Acorde
                                </h4>
                                <button type="button" @click="editingChord = null" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">&times;</button>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-500 mb-1">Nombre del Acorde</label>
                                <input type="text"
                                    x-ref="chordEditInput"
                                    x-model="editingChord.val"
                                    class="w-full font-mono text-xl font-bold text-center h-12 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-amber-600 dark:text-amber-400 focus:ring-2 ring-amber-500/40 outline-none uppercase">
                            </div>

                            <!-- Botones rápidos de notas y variaciones -->
                            <div class="space-y-2">
                                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Notas Base:</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="n in ['C','D','E','F','G','A','B']" :key="n">
                                        <button type="button" @click="editingChord.val = n" class="size-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 font-bold text-xs text-zinc-700 dark:text-zinc-300 hover:bg-amber-500 hover:text-white transition">
                                            <span x-text="n"></span>
                                        </button>
                                    </template>
                                </div>

                                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider pt-1">Sufijos comunes:</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="s in ['m','7','maj7','sus4','add9','#','b','/']" :key="s">
                                        <button type="button" @click="editingChord.val += s" class="px-2 h-7 rounded bg-zinc-100 dark:bg-zinc-800 text-xs font-mono font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                            <span x-text="s"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <button type="button" @click="deleteEditingChord()" class="text-xs text-red-500 hover:text-red-600 font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                    <span>Eliminar</span>
                                </button>
                                <div class="flex gap-2">
                                    <button type="button" @click="editingChord = null" class="px-3 py-1.5 text-xs text-zinc-600 dark:text-zinc-400 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        Cancelar
                                    </button>
                                    <button type="button" @click="saveEditChord()" class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition">
                                        Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Modo Lectura -->
                <div
                    class="flex flex-wrap items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-[#18181b]/90 backdrop-blur-sm sticky top-0 z-10 gap-3">
                    <h3 class="text-zinc-900 dark:text-zinc-100 font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600 dark:text-amber-500">music_note</span>
                        Letra y Acordes (Estilo Cifra)
                    </h3>

                    @if(!$this->pdfUrl)
                        <div class="flex items-center gap-3 flex-wrap">
                            <!-- Botón Editar Acordes y Letra -->
                            <button wire:click="$set('modoEdicion', true)"
                                class="flex items-center gap-1.5 h-8 px-3 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-xs border border-amber-500/30 transition-colors shadow-2xs"
                                title="Editar letra y acordes interactivamente"
                                type="button">
                                <span class="material-symbols-outlined text-[16px]">tune</span>
                                <span>Editar Acordes y Letra</span>
                            </button>

                            <!-- Control de Tonalidad -->
                            <div class="flex items-center gap-1.5 bg-white dark:bg-zinc-900 rounded-lg p-1 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                                <button wire:click="cambiarTono(-1)"
                                    class="size-8 flex items-center justify-center rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors"
                                    title="Bajar medio tono (-1)"
                                    type="button">
                                    <span class="material-symbols-outlined text-[18px]">remove</span>
                                </button>
                                <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400 px-2 min-w-[2.5rem] text-center" title="Tono actual">{{ $tonoActual }}</span>
                                <button wire:click="cambiarTono(1)"
                                    class="size-8 flex items-center justify-center rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors"
                                    title="Subir medio tono (+1)"
                                    type="button">
                                    <span class="material-symbols-outlined text-[18px]">add</span>
                                </button>
                            </div>

                            @if($this->transposicion !== 0)
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="guardarTonoTranspuesto"
                                        class="flex items-center gap-1.5 h-8 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors"
                                        title="Guardar permanente: fija {{ $tonoActual }} como tono original y transpone los acordes en la canción"
                                        type="button">
                                        <span class="material-symbols-outlined text-[16px]">save</span>
                                        <span>Fijar en {{ $tonoActual }}</span>
                                    </button>
                                    <button wire:click="restablecerTono"
                                        class="flex items-center justify-center size-8 rounded-lg bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors"
                                        title="Restablecer al tono base ({{ $cancion->tono_original }})"
                                        type="button">
                                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Control de Tamaño de Letra -->
                            <div class="flex items-center gap-1 bg-white dark:bg-zinc-900 rounded-lg p-1 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                                <button wire:click="cambiarTamanioLetra(-2)"
                                    class="h-8 px-2 flex items-center justify-center rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold text-xs transition-colors"
                                    title="Achicar letra"
                                    type="button">
                                    A-
                                </button>
                                <span class="text-xs font-mono text-zinc-400 px-1">{{ $tamanioLetra }}px</span>
                                <button wire:click="cambiarTamanioLetra(2)"
                                    class="h-8 px-2 flex items-center justify-center rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold text-xs transition-colors"
                                    title="Agrandar letra"
                                    type="button">
                                    A+
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="p-0 h-[800px] w-full bg-white dark:bg-[#18181b]">
                    @if($this->pdfUrl)
                        <iframe src="{{ $this->pdfUrl }}" class="w-full h-full" frameborder="0"></iframe>
                    @else
                        <div class="p-6 md:p-8 overflow-y-auto max-h-[800px] font-mono select-text bg-white dark:bg-[#18181b]">
                            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 border-b border-zinc-200 dark:border-zinc-800 pb-3">{{ $cancion->titulo }}</h2>
                            <div>
                                {!! $this->renderLetraConAcordes($cancion->letra) !!}
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
            <div
                class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] p-4 flex flex-col gap-3 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-cover bg-center shrink-0 shadow-sm"
                        data-alt="Abstract album art cover with blue and purple gradients"
                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBKGmAjgmzYKHvzXf7T7TZiUFSVQqc5nmnTIWQi6F4M7XuUw_6uATukUdeJ1tDgJYOF7oQvldG2biLUFtmN_N7nVyO8Wifre7OtAXdsqfI9YJv5I76UJwUF3kLsl23ms82olGb2IgKOHfJvxxsmuVjZBkl98Y_E-cQZRt4RNP4c56Dx_5ovdPBAli806kz8qsQVzCHNFMZMdVxQEQgrUjCaOAC3S65DWWfGYGAnJNJuumQWbQBEaWylajKj6DGb04WKWU6nTcwGczI8");'>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-900 dark:text-white text-sm font-bold truncate">{{ $cancion->titulo }}</p>
                        <p class="text-slate-500 dark:text-[#9dabb9] text-xs truncate">Tono Original:
                            {{ $cancion->tono_original }}
                        </p>
                    </div>
                </div>
                <div
                    class="w-full bg-gray-200 dark:bg-[#283039] h-1.5 rounded-full overflow-hidden mt-1 cursor-pointer">
                    <div class="bg-primary h-full w-1/3 rounded-full"></div>
                </div>
                <div class="flex justify-between items-center text-slate-400 dark:text-[#9dabb9] text-xs font-mono">
                    <span>1:12</span>
                    <span>4:23</span>
                </div>
                <div class="flex justify-center items-center gap-4">
                    <button
                        class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white transition-colors"
                        type="button"><span class="material-symbols-outlined">skip_previous</span></button>
                    <button
                        class="size-10 flex items-center justify-center rounded-full bg-primary text-white shadow-lg hover:bg-primary/90 transition transform hover:scale-105"
                        type="button">
                        <span class="material-symbols-outlined material-symbols-filled">play_arrow</span>
                    </button>
                    <button
                        class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white transition-colors"
                        type="button"><span class="material-symbols-outlined">skip_next</span></button>
                </div>
            </div>

            <div
                class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
                <div
                    class="px-4 py-3 border-b border-gray-200 dark:border-[#283039] flex justify-between items-center bg-gray-50 dark:bg-[#1c2128]">
                    <h3 class="text-slate-900 dark:text-white font-bold text-sm">Archivos y Partituras</h3>

                    <flux:dropdown>
                        <flux:button size="sm" icon="plus" variant="ghost">Agregar</flux:button>

                        <flux:menu>
                            <flux:menu.item icon="document-text"
                                wire:click="$dispatch('modal-show', { name: 'modal-interactivo' })">
                                Interactivo
                            </flux:menu.item>
                            <flux:menu.item icon="folder-open"
                                wire:click="$dispatch('modal-show', { name: 'modal-recurso' })">
                                Recurso
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
                <div class="flex flex-col">
                    @forelse($cancion->recursos as $recurso)
                        <div
                            class="group flex items-center justify-between p-3 border-b border-gray-100 dark:border-[#283039] hover:bg-gray-50 dark:hover:bg-[#283039]/50 transition cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div
                                    class="size-8 rounded bg-blue-100 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-500">
                                    <span class="material-symbols-outlined text-[20px]">
                                        @if($recurso->tipo == 'pdf') picture_as_pdf
                                        @elseif($recurso->tipo == 'audio') audio_file
                                        @else description @endif
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-slate-900 dark:text-white text-sm font-medium">
                                        {{ $recurso->etiqueta ?? 'Recurso' }}
                                    </p>
                                    <p class="text-slate-500 dark:text-[#9dabb9] text-xs uppercase">{{ $recurso->tipo }}</p>
                                </div>
                            </div>
                            <a href="{{ $recurso->url }}" target="_blank"
                                class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white opacity-0 group-hover:opacity-100 transition">
                                <span class="material-symbols-outlined">download</span>
                            </a>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-500 dark:text-slate-400 text-sm">
                            No hay recursos disponibles.
                        </div>
                    @endforelse
                </div>
            </div>

            <div
                class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-[#283039] bg-gray-50 dark:bg-[#1c2128]">
                    <h3 class="text-slate-900 dark:text-white font-bold text-sm">Tutoriales y Videos</h3>
                </div>
                <div class="p-3 grid grid-cols-2 gap-3">
                    <div class="col-span-2 text-center text-slate-500 text-xs py-4">Proximamente videos</div>
                </div>
            </div>

            <label
                class="rounded-xl border-2 border-dashed border-gray-300 dark:border-[#283039] bg-gray-50 dark:bg-[#1c2128]/50 hover:bg-white dark:hover:bg-[#1c2128] hover:border-primary/50 dark:hover:border-primary/50 transition p-6 flex flex-col items-center justify-center gap-3 cursor-pointer group">
                <input class="hidden" multiple="" type="file" />
                <div
                    class="size-10 rounded-full bg-gray-200 dark:bg-[#283039] group-hover:bg-primary/10 dark:group-hover:bg-primary/20 flex items-center justify-center text-slate-500 dark:text-[#9dabb9] group-hover:text-primary transition">
                    <span class="material-symbols-outlined">cloud_upload</span>
                </div>
                <div class="text-center">
                    <p class="text-slate-900 dark:text-white text-sm font-bold">Subir Recurso</p>
                    <p class="text-slate-500 dark:text-[#9dabb9] text-xs">Arrastra archivos aquí o haz clic
                    </p>
                </div>
            </label>
        </div>
    </div>

    <livewire:canciones.editar />

    <flux:modal name="modal-interactivo" class="md:w-[600px] space-y-6">
        <div class="space-y-2">
            <flux:heading size="lg">Editor Interactivo</flux:heading>
            <flux:subheading>Escribe la letra y añade acordes entre corchetes, ej: [C] Letra</flux:subheading>
        </div>

        <flux:textarea wire:model="letra" label="Letra con Acordes" rows="15"
            placeholder="[C] Amazing grace [F] how sweet the sound..." />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button wire:click="guardarLetra" variant="primary">Guardar</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="modal-recurso" class="md:w-[500px] space-y-6">
        <div class="space-y-2">
            <flux:heading size="lg">Subir Recurso</flux:heading>
            <flux:subheading>Sube un PDF, Audio o Imagen.</flux:subheading>
        </div>
        <div class="p-4 text-center text-gray-500">
            Funcionalidad de subida de archivos pendiente.
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cerrar</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    <script>
        (function() {
            function registerEditor() {
                if (window.Alpine) {
                    Alpine.data('editorAcordes', (config) => ({
                        tab: 'visual',
                        rawText: config.initialText || '',
                        lines: [],
                        selectedChord: 'A',
                        customChordInput: '',
                        editingChord: null,
                        draggedChord: null,
                        dragOverLineIdx: null,
                        dragOverCol: null,
                        charWidth: 9.6,

                        paletteChords: [
                            'A', 'A7', 'Am', 'B', 'B7', 'Bm', 'C', 'C7', 'C#m', 'D', 'D7', 'Dm',
                            'E', 'E7', 'Em', 'F', 'F#m', 'G', 'G7', 'Gm', 'Bb', 'Eb', 'Ab'
                        ],

                        init() {
                            this.parseText(this.rawText);
                            this.$nextTick(() => {
                                this.updateCharWidth();
                            });
                            window.addEventListener('resize', () => this.updateCharWidth());

                            this.$watch('tab', (newTab) => {
                                if (newTab === 'raw') {
                                    this.rawText = this.serializeToText();
                                } else {
                                    this.parseText(this.rawText);
                                    this.$nextTick(() => this.updateCharWidth());
                                }
                            });
                        },

                        updateCharWidth() {
                            const el = this.$refs.measureSpan;
                            if (el) {
                                const rect = el.getBoundingClientRect();
                                if (rect.width > 0) {
                                    this.charWidth = rect.width / 20;
                                }
                            }
                        },

                        isAcordeToken(token) {
                            token = (token || '').trim();
                            if (!token || token === '//' || token === '/' || token === '||' || token === '|') return true;
                            return /^[A-G][b#]?(m|maj|min|dim|aug|sus[24]?|add[0-9]+|[0-9]+|b[0-9]+|#[0-9]+|\+|\*|°|ø|-)*(\/[A-G][b#]?)?$/i.test(token);
                        },

                        isLineaDeAcordes(linea) {
                            const palabras = linea.trim().split(/\s+/).filter(Boolean);
                            if (!palabras.length) return false;
                            let count = 0;
                            for (const p of palabras) {
                                if (this.isAcordeToken(p)) count++;
                            }
                            return (count / palabras.length) >= 0.7;
                        },

                        parseText(text) {
                            const rawLines = (text || '').split('\n');
                            const parsed = [];
                            let i = 0;

                            while (i < rawLines.length) {
                                const line = rawLines[i].replace(/\r$/, '');
                                const trimmed = line.trim();

                                if (!trimmed) {
                                    parsed.push({ id: Math.random(), type: 'blank' });
                                    i++;
                                    continue;
                                }

                                const headerMatch = line.match(/^\s*(\[[^\]]+\])\s*$/);
                                if (headerMatch) {
                                    parsed.push({ id: Math.random(), type: 'header', text: headerMatch[1] });
                                    i++;
                                    continue;
                                }

                                if (this.isLineaDeAcordes(line)) {
                                    const chords = [];
                                    const re = /\S+/g;
                                    let m;
                                    while ((m = re.exec(line)) !== null) {
                                        chords.push({
                                            id: Math.random(),
                                            chord: m[0],
                                            col: m.index
                                        });
                                    }

                                    let nextLineText = '';
                                    if (i + 1 < rawLines.length) {
                                        const nextLine = rawLines[i + 1].replace(/\r$/, '');
                                        const nextTrimmed = nextLine.trim();
                                        if (nextTrimmed && !nextLine.match(/^\s*(\[[^\]]+\])\s*$/) && !this.isLineaDeAcordes(nextLine)) {
                                            nextLineText = nextLine;
                                            i++;
                                        }
                                    }

                                    parsed.push({
                                        id: Math.random(),
                                        type: 'lyric',
                                        text: nextLineText,
                                        chords: chords
                                    });
                                    i++;
                                    continue;
                                }

                                if (line.includes('[')) {
                                    let cleanText = '';
                                    const chords = [];
                                    const parts = line.split(/(\[[^\]]+\])/);
                                    let pendingChord = null;

                                    for (const part of parts) {
                                        const chordMatch = part.match(/^\[([^\]]+)\]$/);
                                        if (chordMatch) {
                                            pendingChord = chordMatch[1];
                                        } else {
                                            if (pendingChord) {
                                                chords.push({
                                                    id: Math.random(),
                                                    chord: pendingChord,
                                                    col: cleanText.length
                                                });
                                                pendingChord = null;
                                            }
                                            cleanText += part;
                                        }
                                    }
                                    if (pendingChord) {
                                        chords.push({
                                            id: Math.random(),
                                            chord: pendingChord,
                                            col: cleanText.length
                                        });
                                    }

                                    parsed.push({
                                        id: Math.random(),
                                        type: 'lyric',
                                        text: cleanText,
                                        chords: chords
                                    });
                                    i++;
                                    continue;
                                }

                                parsed.push({
                                    id: Math.random(),
                                    type: 'lyric',
                                    text: line,
                                    chords: []
                                });
                                i++;
                            }

                            this.lines = parsed;
                        },

                        serializeToText() {
                            const out = [];
                            for (const line of this.lines) {
                                if (line.type === 'blank') {
                                    out.push('');
                                } else if (line.type === 'header') {
                                    out.push(line.text);
                                } else if (line.type === 'lyric') {
                                    if (!line.chords || line.chords.length === 0) {
                                        out.push(line.text || '');
                                    } else {
                                        const sorted = [...line.chords].sort((a, b) => a.col - b.col);
                                        let chordLine = '';
                                        let cur = 0;
                                        for (const c of sorted) {
                                            const targetCol = Math.max(0, c.col);
                                            if (targetCol > cur) {
                                                chordLine += ' '.repeat(targetCol - cur);
                                                cur = targetCol;
                                            } else if (targetCol < cur && cur > 0) {
                                                chordLine += ' ';
                                                cur++;
                                            }
                                            chordLine += c.chord;
                                            cur += c.chord.length;
                                        }
                                        out.push(chordLine);
                                        out.push(line.text || '');
                                    }
                                }
                            }
                            return out.join('\n');
                        },

                        calculateCol(event, el) {
                            const rect = el.getBoundingClientRect();
                            const x = Math.max(0, event.clientX - rect.left);
                            this.updateCharWidth();
                            return Math.max(0, Math.round(x / this.charWidth));
                        },

                        startDragChord(lineIdx, chordIdx, event) {
                            this.draggedChord = {
                                source: 'line',
                                lineIdx: lineIdx,
                                chordIdx: chordIdx,
                                chordName: this.lines[lineIdx].chords[chordIdx].chord
                            };
                            event.dataTransfer.setData('text/plain', this.draggedChord.chordName);
                            event.dataTransfer.effectAllowed = 'move';
                        },

                        startDragPalette(chordName, event) {
                            this.draggedChord = {
                                source: 'palette',
                                chordName: chordName
                            };
                            event.dataTransfer.setData('text/plain', chordName);
                            event.dataTransfer.effectAllowed = 'copy';
                        },

                        onDragOver(lineIdx, event) {
                            this.dragOverLineIdx = lineIdx;
                            this.dragOverCol = this.calculateCol(event, event.currentTarget);
                        },

                        onDragLeave(lineIdx, event) {
                            if (this.dragOverLineIdx === lineIdx) {
                                this.dragOverLineIdx = null;
                                this.dragOverCol = null;
                            }
                        },

                        onDrop(lineIdx, event) {
                            const col = this.calculateCol(event, event.currentTarget);
                            this.dragOverLineIdx = null;
                            this.dragOverCol = null;

                            if (!this.draggedChord) return;

                            const targetLine = this.lines[lineIdx];
                            if (!targetLine || targetLine.type !== 'lyric') return;

                            if (this.draggedChord.source === 'palette') {
                                targetLine.chords.push({
                                    id: Math.random(),
                                    chord: this.draggedChord.chordName,
                                    col: col
                                });
                            } else if (this.draggedChord.source === 'line') {
                                const srcLine = this.lines[this.draggedChord.lineIdx];
                                const [moved] = srcLine.chords.splice(this.draggedChord.chordIdx, 1);
                                if (moved) {
                                    moved.col = col;
                                    targetLine.chords.push(moved);
                                }
                            }

                            targetLine.chords.sort((a, b) => a.col - b.col);
                            this.draggedChord = null;
                        },

                        onTrackClick(lineIdx, event) {
                            if (event.target.closest('[data-chord-chip]')) return;
                            const col = this.calculateCol(event, event.currentTarget);
                            const line = this.lines[lineIdx];
                            if (!line || line.type !== 'lyric') return;

                            const newChord = {
                                id: Math.random(),
                                chord: this.selectedChord || 'A',
                                col: col
                            };
                            line.chords.push(newChord);
                            line.chords.sort((a, b) => a.col - b.col);
                            const newIdx = line.chords.indexOf(newChord);
                            this.openEditChord(lineIdx, newIdx);
                        },

                        openEditChord(lineIdx, chordIdx) {
                            const chord = this.lines[lineIdx].chords[chordIdx];
                            this.editingChord = {
                                lineIdx: lineIdx,
                                chordIdx: chordIdx,
                                val: chord.chord
                            };
                            this.$nextTick(() => {
                                if (this.$refs.chordEditInput) {
                                    this.$refs.chordEditInput.focus();
                                    this.$refs.chordEditInput.select();
                                }
                            });
                        },

                        saveEditChord() {
                            if (!this.editingChord) return;
                            const val = (this.editingChord.val || '').trim();
                            const line = this.lines[this.editingChord.lineIdx];
                            if (val && line && line.chords[this.editingChord.chordIdx]) {
                                line.chords[this.editingChord.chordIdx].chord = val;
                                this.selectedChord = val;
                            }
                            this.editingChord = null;
                        },

                        deleteEditingChord() {
                            if (!this.editingChord) return;
                            const line = this.lines[this.editingChord.lineIdx];
                            if (line && line.chords) {
                                line.chords.splice(this.editingChord.chordIdx, 1);
                            }
                            this.editingChord = null;
                        },

                        deleteChord(lineIdx, chordIdx) {
                            const line = this.lines[lineIdx];
                            if (line && line.chords) {
                                line.chords.splice(chordIdx, 1);
                            }
                        },

                        addCustomPaletteChord() {
                            const val = (this.customChordInput || '').trim();
                            if (val && !this.paletteChords.includes(val)) {
                                this.paletteChords.push(val);
                                this.selectedChord = val;
                            }
                            this.customChordInput = '';
                        },

                        addLine(afterIdx) {
                            this.lines.splice(afterIdx + 1, 0, {
                                id: Math.random(),
                                type: 'lyric',
                                text: '',
                                chords: []
                            });
                        },

                        addHeader(afterIdx, headerName) {
                            this.lines.splice(afterIdx + 1, 0, {
                                id: Math.random(),
                                type: 'header',
                                text: `[${headerName}]`
                            });
                            this.lines.splice(afterIdx + 2, 0, {
                                id: Math.random(),
                                type: 'lyric',
                                text: '',
                                chords: []
                            });
                        },

                        removeLine(lineIdx) {
                            this.lines.splice(lineIdx, 1);
                            if (this.lines.length === 0) {
                                this.addLine(-1);
                            }
                        },

                        saveChanges(wire) {
                            const final = this.tab === 'raw' ? this.rawText : this.serializeToText();
                            const target = wire || this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                            if (target && typeof target.guardarLetra === 'function') {
                                target.guardarLetra(final);
                            }
                        }
                    }));
                } else {
                    document.addEventListener('alpine:init', registerEditor);
                }
            }
            registerEditor();
        })();
    </script>
</div>
