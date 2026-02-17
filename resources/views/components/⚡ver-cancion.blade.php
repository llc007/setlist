<?php

use App\Models\Cancion;
use Livewire\Component;

new class extends Component {
    public Cancion $cancion;

    // Variables para futura lógica de transposición (visual por ahora)
    public $transposicion = 0;
    public $tonoActual;
    public $letra; // Propiedad para el editor de letra

    public function mount(Cancion $cancion)
    {
        $this->cancion = $cancion->load('recursos', 'categoria');
        $this->tonoActual = $cancion->tono_original ?? 'C';
        $this->letra = $cancion->letra;
    }

    public function guardarLetra()
    {
        $this->cancion->update(['letra' => $this->letra]);
        $this->dispatch('modal-close', name: 'modal-interactivo');
        // Opcional: Notificar éxito
    }

    // Aquí iría la lógica para subir/bajar tono más adelante
    public function cambiarTono($cantidad)
    {
        $this->transposicion += $cantidad;
        // $this->tonoActual = ... lógica musical compleja ...
    }

    public function getPdfUrlProperty()
    {
        // 1. Prioridad: Archivo subido directamente (pdf_path)
        if ($this->cancion->pdf_path) {
            // Si es URL externa (http/https), usarla directamente
            if (filter_var($this->cancion->pdf_path, FILTER_VALIDATE_URL)) {
                return $this->formatGoogleDriveUrl($this->cancion->pdf_path);
            }
            // Si es ruta local, usar Storage
            return \Illuminate\Support\Facades\Storage::url($this->cancion->pdf_path);
        }

        // 2. Prioridad: Buscar en los recursos (tipo pdf)
        $recursoPdf = $this->cancion->recursos->where('tipo', 'pdf')->first();
        if ($recursoPdf) {
            return $this->formatGoogleDriveUrl($recursoPdf->url);
        }

        return null;
    }

    private function formatGoogleDriveUrl($url)
    {
        // Transformar URL de Google Drive de 'view' a 'preview' para iframe
        if (str_contains($url, 'drive.google.com') && str_contains($url, '/view')) {
            return str_replace('/view', '/preview', $url);
        }
        return $url;
    }

    public function renderLetraConAcordes($texto)
    {
        if (!$texto)
            return '';

        // Escapar HTML por seguridad
        $texto = htmlspecialchars($texto);
        $lineas = explode("\n", $texto);
        $html = '<div class="space-y-3 font-sans">';

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if (empty($linea)) {
                $html .= '<div class="h-4"></div>'; // Salto de línea visual
                continue;
            }

            // Detectar automáticamente cabeceras de secciones
            if (preg_match('/^(Intro|Coro|Verso|Puente|Bridge|Chorus|Pre-Coro|Outro|Final)/i', $linea)) {
                $html .= '<div class="font-black text-primary text-lg mt-6 mb-1">' . $linea . '</div>';
                continue;
            }

            // Contenedor de la línea con flex-wrap para que se adapte a móviles
            $html .= '<div class="flex flex-wrap items-end">';

            // Separar la línea usando los corchetes como delimitadores
            $partes = preg_split('/(\[[^\]]+\])/', $linea, -1, PREG_SPLIT_DELIM_CAPTURE);
            $acordeActual = '';

            foreach ($partes as $parte) {
                if (preg_match('/\[([^\]]+)\]/', $parte, $coincidencias)) {
                    // Es un acorde, lo guardamos para la siguiente palabra
                    $acordeActual = $coincidencias[1];
                } else {
                    // Es letra: la dividimos por espacios para asegurar un buen "wrap" en móviles
                    $palabras = explode(' ', $parte);

                    foreach ($palabras as $index => $palabra) {
                        // Respetar el espacio entre palabras
                        $espacio = ($index < count($palabras) - 1) ? '&nbsp;' : '';
                        $html
                            .= '<div class="inline-flex flex-col justify-end text-left">'; // Solo la primera palabra de este bloque
                        //lleva el acorde 
                        $acordeAMostrar = ($index === 0) ? $acordeActual : ''; // Acorde (Fila superior) 
                        $html .= '<span class="font-bold text-primary text-sm h-5 leading-none">' . $acordeAMostrar . '</span>'; // Letra
                        // (Fila inferior) 
                        $html .= '<span class="text-slate-800 dark:text-slate-200 text-lg md:text-xl leading-tight">'
                            . $palabra . $espacio . '</span>';
                        $html .= '</div>';
                    }
                    $acordeActual = ''; // Limpiamos el acorde después
                    //de usarlo 
                }
            }
            $html .= '</div>';
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
            <button
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm"
                type="button">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span class="hidden sm:inline">Imprimir</span>
            </button>
            <button wire:click="$dispatch('abrir-modal-edicion', { id: {{ $cancion->id }} })"
                class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20"
                type="button">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                <span>Editar</span>
            </button>
        </div>
    </div>

    <div class="flex flex-wrap justify-between items-end gap-6 pb-6 border-b border-gray-200 dark:border-[#283039]">
        <div class="flex flex-col gap-2">
            <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black tracking-tight">
                {{$cancion->codigo . " - " . $cancion->titulo }}
            </h1>
            <div class="flex items-center gap-2 text-slate-500 dark:text-[#9dabb9]">
                <span class="material-symbols-outlined text-[20px]">mic</span>
                <p class="text-lg font-normal">{{ $cancion->artista }}</p>
            </div>
        </div>
        <button
            class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-slate-900 dark:bg-white text-white dark:text-black text-sm font-bold hover:bg-slate-800 dark:hover:bg-gray-200 transition-colors shadow-md"
            type="button">
            <span class="material-symbols-outlined text-[20px] material-symbols-filled">slideshow</span>
            <span>Modo Presentación</span>
        </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Tonalidad</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">{{ $cancion->tono_original }}</p>
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                BPM</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">72</p> <!-- TODO: Add BPM to model -->
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Compás</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">4/4</p>
            <!-- TODO: Add Time Signature to model -->
        </div>
        <div
            class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                Duración</p>
            <p class="text-slate-900 dark:text-white text-xl font-bold">4:23</p> <!-- TODO: Add Duration to model -->
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full min-h-[600px] mt-6">
        <div
            class="lg:col-span-7 xl:col-span-8 flex flex-col bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
            <div
                class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-[#283039] bg-white/50 dark:bg-[#1c2128]/50 backdrop-blur-sm sticky top-0 z-10">
                <h3 class="text-slate-900 dark:text-white font-bold text-lg">Letra y Acordes</h3>

                @if(!$this->pdfUrl)
                    <div
                        class="flex items-center gap-2 bg-gray-100 dark:bg-[#111418] rounded-lg p-1 border border-gray-200 dark:border-[#283039]">
                        <button wire:click="cambiarTono(-1)"
                            class="size-8 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                            type="button">
                            <span class="material-symbols-outlined text-[20px]">remove</span>
                        </button>
                        <span
                            class="text-xs font-mono font-bold text-primary px-2 min-w-[3rem] text-center">{{ $tonoActual }}</span>
                        <button wire:click="cambiarTono(1)"
                            class="size-8 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                            type="button">
                            <span class="material-symbols-outlined text-[20px]">add</span>
                        </button>
                    </div>
                @endif

                <div class="hidden sm:flex gap-1">
                    <button
                        class="size-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                        title="Toggle Chords" type="button">
                        <span class="material-symbols-outlined text-[20px]">music_note</span>
                    </button>
                    <button
                        class="size-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                        title="Settings" type="button">
                        <span class="material-symbols-outlined text-[20px]">settings</span>
                    </button>
                </div>
            </div>
            <div class="p-0 h-[800px] w-full bg-white">
                @if($this->pdfUrl)
                    <iframe src="{{ $this->pdfUrl }}" class="w-full h-full" frameborder="0"></iframe>
                @else
                    <div class="p-6 md:p-8 overflow-y-auto max-h-[800px]">
                        <div
                            class="font-mono text-base md:text-lg leading-loose text-slate-700 dark:text-white/90 whitespace-pre-wrap">
                            {!! $this->renderLetraConAcordes($cancion->letra) !!}
                        </div>
                    </div>
                @endif
            </div>
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
                    <!-- TODO: Add video functionality to model/resources -->
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

    <livewire:editar-cancion />

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

    <!-- Placeholder for Recurso modal if needed later, or handle file upload logic separately -->
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
</div>