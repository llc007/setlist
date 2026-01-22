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

                @if(!$mostrandoFormulario)
                <!-- Botón para añadir -->
                <flux:button variant="primary" icon="plus" wire:click="$set('mostrandoFormulario', true)">Añadir Recurso</flux:button>
                @endif
            @endif

            @if(count($recursosExistentes) === 0 || $mostrandoFormulario)
                <div wire:transition class="space-y-4 pt-4">
                    <form wire:submit="guardar" class="space-y-4">
                        <flux:radio.group wire:model="tipo" label="Tipo de nuevo recurso" class="flex-col">
                            <flux:radio value="youtube" label="YouTube" icon="video-camera" />
                            <flux:radio value="pdf" label="Partitura / PDF" icon="document-text" />
                        </flux:radio.group>
                        
                        <flux:input wire:model="url" label="URL del recurso" placeholder="https://..." />
                        <flux:input wire:model="etiqueta" label="Nombre corto (Etiqueta)"
                            placeholder="Ej: Acordes Guitarra..." />
                            
                        <div class="flex gap-2 pt-2">
                            <flux:spacer />
                            @if(count($recursosExistentes) > 0)
                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancelar</flux:button>
                                </flux:modal.close>
                            @endif
                            <flux:button type="submit" variant="primary">Guardar Recurso</flux:button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </flux:modal>
</div>