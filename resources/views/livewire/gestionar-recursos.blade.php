<div>
    <flux:modal name="modal-recursos" class="md:w-[400px]">
        <div class="space-y-4">
            @if(count($recursosExistentes) > 0)
                <div class="space-y-2">
                    <p class="text-xs font-bold uppercase text-slate-500">Recursos actuales</p>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 border rounded-lg">
                        @foreach($recursosExistentes as $rec)
                            <div class="p-3 flex items-center justify-between bg-slate-50/50 dark:bg-white/5">
                                <div class="flex items-center gap-3">
                                    <flux:icon.{{ $rec->tipo === 'youtube' ? 'video-camera' : 'document-text' }}
                                        class="size-5 text-primary" />
                                    <span class="text-sm font-medium">{{ $rec->etiqueta }}</span>
                                </div>
                                <flux:button variant="ghost" size="xs" icon="trash" class="text-red-500 cursor-pointer"
                                    wire:click="eliminarRecurso({{ $rec->id }})" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div>
                    <flux:heading size="lg">Añadir Recurso</flux:heading>
                    <flux:subheading>Para: **{{ $tituloCancion }}**</flux:subheading>
                </div>
                <form wire:submit="guardar" class="space-y-4">
                    <flux:radio.group wire:model="tipo" label="Tipo de recurso" class="flex-col">
                        <flux:radio value="youtube" label="YouTube" icon="video-camera" />
                        <flux:radio value="pdf" label="Partitura / PDF" icon="document-text" />
                    </flux:radio.group>
                    <flux:input wire:model="url" label="URL del recurso" placeholder="https://..." />
                    <flux:input wire:model="etiqueta" label="Nombre corto (Etiqueta)"
                        placeholder="Ej: Acordes Guitarra, Video Ensayo..." />
                    <div class="flex gap-2 pt-4">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancelar</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary">Guardar</flux:button>
                    </div>
                </form>
            @endif

        </div>
    </flux:modal>
</div>