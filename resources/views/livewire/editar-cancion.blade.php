<div>
    <flux:modal name="editar-cancion" class="md:w-[500px] space-y-6">
        <div class="space-y-2">
            <flux:heading size="lg">Editar Canción</flux:heading>
            <flux:subheading>Ingresa los detalles básicos de la nueva canción.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:input wire:model="titulo" label="Título de la canción" placeholder="Ej: Cuan Grande es Él" />

            <flux:input wire:model="artista" label="Artista/Autor" placeholder="Ej: Hillsong, Tradicional..." />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="categoria_id" label="Categoría" placeholder="Selecciona...">
                    @foreach ($categorias as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="tono_original" label="Tono" placeholder="Ej: G, Am..." />
            </div>

            <flux:textarea wire:model="letra" label="Letra (Opcional)" placeholder="Pega la letra aquí..." rows="5" />

            <div class="flex gap-2 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">Guardar Canción</flux:button>
            </div>
        </form>
    </flux:modal>
</div>