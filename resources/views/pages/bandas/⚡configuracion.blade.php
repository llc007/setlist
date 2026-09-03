<?php

use App\Models\Banda;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;

    public string $nombre = '';
    public string $slug = '';
    public string $descripcion = '';
    public string $tipo_ambito = 'cristiano';

    public function mount(Banda $banda): void
    {
        $this->banda = $banda;
        session()->put('active_banda_id', $this->banda->id);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('SuperAdministrador');
        $isBandAdmin = $this->banda->miembros()
            ->where('users.id', $user->id)
            ->wherePivot('rol', 'Administrador de Banda')
            ->exists();

        if (! $isSuperAdmin && ! $isBandAdmin) {
            abort(403, 'No tienes permisos para acceder a la configuración de esta banda.');
        }

        $this->nombre = $this->banda->nombre;
        $this->slug = $this->banda->slug;
        $this->descripcion = $this->banda->descripcion ?? '';
        $this->tipo_ambito = $this->banda->tipo_ambito ?? 'cristiano';
    }

    public function updatedNombre(): void
    {
        $this->slug = Str::slug($this->nombre);
    }

    public function saveSettings(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bandas', 'slug')->ignore($this->banda->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'tipo_ambito' => ['required', 'string', 'in:cristiano,secular,mixto'],
        ]);

        $this->banda->update([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['slug']),
            'descripcion' => $validated['descripcion'] ?? null,
            'tipo_ambito' => $validated['tipo_ambito'],
        ]);

        session()->flash('status', 'Configuración de la banda actualizada exitosamente.');

        $this->redirectRoute('bandas.configuracion', ['banda' => $this->banda->slug], navigate: true);
    }

    public function render(): mixed
    {
        return view('pages.bandas.⚡configuracion')->title('Configuración - ' . $this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Configuración de ') . $this->banda->nombre }}</flux:heading>
            <flux:text variant="subtle">{{ __('Ajusta el nombre, ámbito de repertorio y detalles principales de la banda.') }}</flux:text>
        </div>

        <flux:button variant="outline" icon="arrow-left" :href="route('bandas.show', $banda->slug)" wire:navigate>
            {{ __('Volver al Inicio de Banda') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Formulario de Configuración -->
    <div class="max-w-2xl p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <form wire:submit="saveSettings" class="space-y-6">
            <div class="space-y-4">
                <flux:input
                    wire:model.live.debounce.300ms="nombre"
                    label="Nombre de la Banda o Grupo"
                    required
                />

                <flux:input
                    wire:model="slug"
                    label="Slug (Identificador URL)"
                    required
                />

                <flux:textarea
                    wire:model="descripcion"
                    label="Descripción"
                    rows="3"
                    placeholder="Información sobre la banda..."
                />

                <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 space-y-2">
                    <flux:label>{{ __('Tipo / Ámbito del Repertorio') }}</flux:label>
                    <flux:select wire:model="tipo_ambito" required>
                        <flux:select.option value="cristiano">⛪ {{ __('Ministerio / Iglesia / Alabanza (Cristiano)') }}</flux:select.option>
                        <flux:select.option value="secular">🎸 {{ __('Banda Comercial / Eventos / Bares (General)') }}</flux:select.option>
                        <flux:select.option value="mixto">🎶 {{ __('Mixto / Versátil (Ambos repertorios)') }}</flux:select.option>
                    </flux:select>
                    <flux:text variant="subtle" class="text-xs">
                        {{ __('Define qué catálogo público de canciones y categorías verá tu grupo de forma predeterminada.') }}
                    </flux:text>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button type="submit" variant="primary">
                    {{ __('Guardar Configuración') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
