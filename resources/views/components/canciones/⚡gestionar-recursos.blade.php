<?php

use App\Models\Cancion;
use App\Models\CancionRecurso;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $cancionId;
    public $tituloCancion;
    public $tipo = 'youtube';
    public $url = '';
    public $etiqueta = '';
    public $mostrandoFormulario = false;

    #[On('abrir-modal-recursos')]
    public function cargarCancion($id)
    {
        $cancion = Cancion::findOrFail($id);
        $this->cancionId = $cancion->id;
        $this->tituloCancion = $cancion->titulo;
        $this->mostrandoFormulario = false;

        $this->dispatch('modal-show', name: 'modal-recursos');
    }

    public function toggleFormulario()
    {
        $this->mostrandoFormulario = !$this->mostrandoFormulario;
    }

    public function guardar()
    {
        if (empty($this->url) && empty($this->etiqueta)) {
            $this->reset(['url', 'etiqueta', 'tipo']);
            $this->dispatch('modal-close', name: 'modal-recursos');
            return;
        }

        $this->validate([
            'tipo' => 'required',
            'url' => 'required|url',
            'etiqueta' => 'required|min:3',
        ]);

        CancionRecurso::create([
            'cancion_id' => $this->cancionId,
            'tipo' => $this->tipo,
            'url' => $this->url,
            'etiqueta' => $this->etiqueta,
        ]);

        $this->reset(['url', 'etiqueta', 'tipo']);
        $this->dispatch('modal-close', name: 'modal-recursos');
        $this->dispatch('cancion-actualizada');
    }

    public function eliminarRecurso($id)
    {
        CancionRecurso::destroy($id);
        $this->dispatch('cancion-actualizada');
    }

    public function with()
    {
        return [
            'recursosExistentes' => $this->cancionId
                ? CancionRecurso::where('cancion_id', $this->cancionId)->get()
                : [],
        ];
    }
};
?>

<div>
    <flux:modal name="modal-recursos" class="md:w-[550px]">
        <div class="space-y-6">
            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div>
                    <flux:heading size="lg">Recursos de la Canción</flux:heading>
                    <flux:subheading class="text-primary-500 font-medium">{{ $tituloCancion }}</flux:subheading>
                </div>

                <flux:button variant="subtle" size="sm"
                    icon="{{ $mostrandoFormulario ? 'minus' : 'plus' }}"
                    wire:click="toggleFormulario">
                    {{ $mostrandoFormulario ? 'Ver Lista' : 'Añadir Recurso' }}
                </flux:button>
            </div>

            @if ($mostrandoFormulario)
                <form wire:submit="guardar" class="space-y-4 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:radio.group wire:model="tipo" label="Tipo de Recurso" variant="segmented">
                        <flux:radio value="youtube" label="YouTube" />
                        <flux:radio value="pdf" label="PDF / Partitura" />
                    </flux:radio.group>

                    <flux:input wire:model="etiqueta" label="Etiqueta o Descripción" placeholder="Ej: Video Oficial, Pista en Sol..." required />
                    <flux:input wire:model="url" label="Enlace (URL)" placeholder="https://..." required />

                    <div class="flex gap-2 justify-end pt-2">
                        <flux:button variant="ghost" size="sm" wire:click="toggleFormulario">Cancelar</flux:button>
                        <flux:button type="submit" variant="primary" size="sm">Guardar Recurso</flux:button>
                    </div>
                </form>
            @else
                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                    @forelse ($recursosExistentes as $recurso)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-lg border border-zinc-200 dark:border-zinc-700/60">
                            <div class="flex items-center gap-3">
                                @if ($recurso->tipo === 'pdf')
                                    <flux:icon.document-text variant="outline" class="size-5 text-red-500" />
                                @elseif($recurso->tipo === 'youtube')
                                    <flux:icon.video-camera variant="outline" class="size-5 text-red-600" />
                                @endif

                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $recurso->etiqueta }}</div>
                                    <a href="{{ $recurso->url }}" target="_blank" class="text-xs text-zinc-400 hover:underline truncate max-w-[250px] block">
                                        {{ $recurso->url }}
                                    </a>
                                </div>
                            </div>

                            <flux:button variant="ghost" size="xs" icon="trash" class="hover:text-red-500" wire:click="eliminarRecurso({{ $recurso->id }})" />
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400 text-sm">
                            Esta canción no tiene recursos adjuntos aún.
                        </div>
                    @endforelse
                </div>
            @endif

            <div class="flex gap-2 justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Cerrar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
