<?php

use App\Models\Banda;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app.sidebar')] #[Title('Gestión Global de Bandas')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $ambitoFilter = '';

    // Modales
    public bool $showBandaModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingBandaId = null;
    public string $nombre = '';
    public string $slug = '';
    public string $descripcion = '';
    public string $tipo_ambito = 'cristiano';

    public ?int $deletingBandaId = null;
    public string $deletingBandaName = '';
    public int $deletingBandaMembersCount = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAmbitoFilter(): void
    {
        $this->resetPage();
    }

    public function updatedNombre(): void
    {
        if (! $this->editingBandaId) {
            $this->slug = Str::slug($this->nombre);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingBandaId = null;
        $this->nombre = '';
        $this->slug = '';
        $this->descripcion = '';
        $this->tipo_ambito = 'cristiano';
        $this->showBandaModal = true;
    }

    public function openEditModal(int $bandaId): void
    {
        $this->resetErrorBag();
        $banda = Banda::findOrFail($bandaId);
        $this->editingBandaId = $banda->id;
        $this->nombre = $banda->nombre;
        $this->slug = $banda->slug;
        $this->descripcion = $banda->descripcion ?? '';
        $this->tipo_ambito = $banda->tipo_ambito ?? 'cristiano';
        $this->showBandaModal = true;
    }

    public function saveBanda(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bandas', 'slug')->ignore($this->editingBandaId),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'tipo_ambito' => ['required', 'string', 'in:cristiano,secular,mixto'],
        ]);

        if ($this->editingBandaId) {
            $banda = Banda::findOrFail($this->editingBandaId);
            $banda->update([
                'nombre' => $validated['nombre'],
                'slug' => Str::slug($validated['slug']),
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_ambito' => $validated['tipo_ambito'],
            ]);

            session()->flash('status', 'Banda actualizada exitosamente.');
        } else {
            $banda = Banda::create([
                'nombre' => $validated['nombre'],
                'slug' => Str::slug($validated['slug']),
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_ambito' => $validated['tipo_ambito'],
            ]);

            // Asignar al usuario actual como Administrador de la banda
            $banda->miembros()->attach(auth()->id(), ['rol' => 'Administrador de Banda']);

            session()->flash('status', 'Banda creada exitosamente.');
        }

        $this->showBandaModal = false;
    }

    public function confirmDelete(int $bandaId): void
    {
        $banda = Banda::withCount('miembros')->findOrFail($bandaId);
        $this->deletingBandaId = $banda->id;
        $this->deletingBandaName = $banda->nombre;
        $this->deletingBandaMembersCount = $banda->miembros_count;
        $this->showDeleteModal = true;
    }

    public function deleteBanda(): void
    {
        if (! $this->deletingBandaId) {
            return;
        }

        $banda = Banda::findOrFail($this->deletingBandaId);
        $banda->delete();

        $this->deletingBandaId = null;
        $this->deletingBandaName = '';
        $this->deletingBandaMembersCount = 0;
        $this->showDeleteModal = false;

        session()->flash('status', 'Banda eliminada correctamente.');
    }

    public function render(): mixed
    {
        $bandas = Banda::query()
            ->withCount(['miembros', 'repertorio', 'setlists'])
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->when($this->ambitoFilter, fn ($q) => $q->where('tipo_ambito', $this->ambitoFilter))
            ->latest()
            ->paginate(10);

        return view('pages.admin.bandas.⚡index', [
            'bandas' => $bandas,
        ]);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Gestión Global de Bandas') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Administra todas las bandas y grupos registrados en el sistema, ajusta sus nombres y asigna sus ámbitos.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nueva Banda') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Buscar banda por nombre o slug..."
                clearable
            />
        </div>

        <div class="w-full sm:w-56">
            <flux:select wire:model.live="ambitoFilter" placeholder="Todos los Ámbitos">
                <flux:select.option value="">{{ __('Todos los Ámbitos') }}</flux:select.option>
                <flux:select.option value="cristiano">⛪ {{ __('Cristiano') }}</flux:select.option>
                <flux:select.option value="secular">🎸 {{ __('General / Secular') }}</flux:select.option>
                <flux:select.option value="mixto">🎶 {{ __('Mixto') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tabla de Bandas -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="first:!ps-6">{{ __('Banda') }}</flux:table.column>
                <flux:table.column>{{ __('Ámbito') }}</flux:table.column>
                <flux:table.column>{{ __('Miembros') }}</flux:table.column>
                <flux:table.column>{{ __('Repertorio') }}</flux:table.column>
                <flux:table.column>{{ __('Setlists') }}</flux:table.column>
                <flux:table.column align="center" class="last:!pe-6">{{ __('Acciones') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($bandas as $b)
                    <flux:table.row wire:key="banda-admin-{{ $b->id }}">
                        <flux:table.cell class="first:!ps-6 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                    <flux:icon.user-group variant="outline" class="size-5" />
                                </span>
                                <div>
                                    <div class="font-semibold">{{ $b->nombre }}</div>
                                    <div class="text-xs text-zinc-500 font-mono">{{ $b->slug }}</div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $badgeColor = match($b->tipo_ambito) {
                                    'cristiano' => 'purple',
                                    'secular' => 'amber',
                                    default => 'sky',
                                };
                                $label = match($b->tipo_ambito) {
                                    'cristiano' => '⛪ Cristiano',
                                    'secular' => '🎸 General / Secular',
                                    default => '🎶 Mixto',
                                };
                            @endphp
                            <flux:badge color="{{ $badgeColor }}" size="sm">{{ $label }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-xs font-semibold">
                            {{ $b->miembros_count }} {{ __('integrantes') }}
                        </flux:table.cell>

                        <flux:table.cell class="text-xs font-semibold">
                            {{ $b->repertorio_count }} {{ __('canciones') }}
                        </flux:table.cell>

                        <flux:table.cell class="text-xs font-semibold">
                            {{ $b->setlists_count }} {{ __('setlists') }}
                        </flux:table.cell>

                        <flux:table.cell align="center" class="last:!pe-6">
                            <div class="flex items-center justify-center gap-2">
                                <flux:button
                                    variant="ghost"
                                    icon="eye"
                                    size="sm"
                                    :href="route('bandas.show', $b->slug)"
                                    wire:navigate
                                    title="Ver Banda"
                                />

                                <flux:button
                                    variant="ghost"
                                    icon="pencil-square"
                                    size="sm"
                                    wire:click="openEditModal({{ $b->id }})"
                                    title="Editar Banda"
                                />

                                <flux:button
                                    variant="ghost"
                                    icon="trash"
                                    size="sm"
                                    class="text-red-500 hover:text-red-700"
                                    wire:click="confirmDelete({{ $b->id }})"
                                    title="Eliminar Banda"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">
                            {{ __('No se encontraron bandas registradas.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $bandas->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Banda -->
    <flux:modal wire:model="showBandaModal" name="banda-modal" class="max-w-md">
        <form wire:submit="saveBanda" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingBandaId ? __('Editar Banda') : __('Crear Nueva Banda') }}
                </flux:heading>
                <flux:text variant="subtle">
                    {{ $editingBandaId ? __('Actualiza el nombre, slug y ámbito de la banda.') : __('Ingresa la información para dar de alta una nueva banda.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input
                    wire:model.live.debounce.300ms="nombre"
                    label="Nombre de la Banda"
                    placeholder="Ej: Banda Alabanza Central"
                    required
                />

                <flux:input
                    wire:model="slug"
                    label="Slug (URL Identificador)"
                    placeholder="banda-alabanza-central"
                    required
                />

                <flux:textarea
                    wire:model="descripcion"
                    label="Descripción"
                    placeholder="Información breve sobre la banda..."
                    rows="3"
                />

                <flux:select wire:model="tipo_ambito" label="Tipo / Ámbito del Grupo" required>
                    <flux:select.option value="cristiano">⛪ {{ __('Ministerio / Iglesia / Alabanza (Cristiano)') }}</flux:select.option>
                    <flux:select.option value="secular">🎸 {{ __('Banda Comercial / Eventos / Bares (General)') }}</flux:select.option>
                    <flux:select.option value="mixto">🎶 {{ __('Mixto / Versátil (Ambos repertorios)') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showBandaModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingBandaId ? __('Guardar Cambios') : __('Crear Banda') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Confirmar Eliminación -->
    <flux:modal wire:model="showDeleteModal" name="delete-banda-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400 flex items-center gap-2">
                    <flux:icon.exclamation-triangle variant="outline" />
                    {{ __('Eliminar Banda') }}
                </flux:heading>
                <flux:text variant="subtle" class="mt-2">
                    ¿Estás seguro de que deseas eliminar la banda <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingBandaName }}</strong>?
                    @if ($deletingBandaMembersCount > 0)
                        <span class="block mt-2 text-red-600 dark:text-red-400 font-semibold">
                            ⚠️ Esta banda tiene {{ $deletingBandaMembersCount }} integrante(s). Todos sus setlists y vinculaciones de canciones también serán eliminados.
                        </span>
                    @endif
                </flux:text>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteBanda">
                    {{ __('Eliminar Banda') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
