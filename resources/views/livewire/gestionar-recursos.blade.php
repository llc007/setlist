<div>
    <flux:modal name="modal-recursos" class="md:w-[400px]">
        <div class="space-y-4">
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
        </div>
    </flux:modal>
</div>