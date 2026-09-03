<?php

use App\Models\Categoria;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app.sidebar')] #[Title('Gestión de Categorías')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $ambitoFilter = '';

    // Modales
    public bool $showCategoryModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingCategoryId = null;
    public string $nombre = '';
    public string $slug = '';
    public ?int $parent_id = null;
    public string $ambito = 'cristiano';

    public ?int $deletingCategoryId = null;
    public string $deletingCategoryName = '';
    public int $deletingCategorySongsCount = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAmbitoFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingCategoryId = null;
        $this->nombre = '';
        $this->slug = '';
        $this->parent_id = null;
        $this->ambito = 'cristiano';
        $this->showCategoryModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        $this->resetErrorBag();
        $categoria = Categoria::findOrFail($categoryId);
        $this->editingCategoryId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->slug = $categoria->slug;
        $this->parent_id = $categoria->parent_id;
        $this->ambito = $categoria->ambito ?? 'cristiano';
        $this->showCategoryModal = true;
    }

    public function updatedNombre(): void
    {
        if (! $this->editingCategoryId) {
            $this->slug = Str::slug($this->nombre);
        }
    }

    public function saveCategory(): void
    {
        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'slug')->ignore($this->editingCategoryId),
            ],
            'parent_id' => ['nullable', 'exists:categorias,id'],
            'ambito' => ['required', 'string', 'in:cristiano,secular,ambos'],
        ]);

        $parentId = ! empty($validated['parent_id']) ? (int) $validated['parent_id'] : null;

        // Evitar que una categoría sea su propia madre
        if ($this->editingCategoryId && $parentId === $this->editingCategoryId) {
            $this->addError('parent_id', 'Una categoría no puede ser su propia categoría padre.');
            return;
        }

        if ($this->editingCategoryId) {
            $categoria = Categoria::findOrFail($this->editingCategoryId);
            $categoria->update([
                'nombre' => $validated['nombre'],
                'slug' => Str::slug($validated['slug']),
                'parent_id' => $parentId,
                'ambito' => $validated['ambito'],
            ]);

            session()->flash('status', 'Categoría actualizada exitosamente.');
        } else {
            Categoria::create([
                'nombre' => $validated['nombre'],
                'slug' => Str::slug($validated['slug']),
                'parent_id' => $parentId,
                'ambito' => $validated['ambito'],
            ]);

            session()->flash('status', 'Categoría creada exitosamente.');
        }

        $this->showCategoryModal = false;
    }

    public function confirmDelete(int $categoryId): void
    {
        $categoria = Categoria::withCount('canciones')->findOrFail($categoryId);
        $this->deletingCategoryId = $categoria->id;
        $this->deletingCategoryName = $categoria->nombre;
        $this->deletingCategorySongsCount = $categoria->canciones_count;
        $this->showDeleteModal = true;
    }

    public function deleteCategory(): void
    {
        if (! $this->deletingCategoryId) {
            return;
        }

        $categoria = Categoria::findOrFail($this->deletingCategoryId);

        // Desasociar subcategorías hijas
        Categoria::where('parent_id', $categoria->id)->update(['parent_id' => null]);

        // Desasociar canciones
        $categoria->canciones()->update(['categoria_id' => null]);
        $categoria->delete();

        $this->deletingCategoryId = null;
        $this->deletingCategoryName = '';
        $this->deletingCategorySongsCount = 0;
        $this->showDeleteModal = false;

        session()->flash('status', 'Categoría eliminada correctamente.');
    }

    public function render(): mixed
    {
        $categorias = Categoria::query()
            ->with(['parent'])
            ->withCount('canciones')
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->when($this->ambitoFilter, fn ($q) => $q->where('ambito', $this->ambitoFilter))
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        $parentCategorias = Categoria::whereNull('parent_id')
            ->when($this->editingCategoryId, fn ($q) => $q->where('id', '!=', $this->editingCategoryId))
            ->orderBy('nombre')
            ->get();

        return view('pages.admin.categorias.⚡index', [
            'categorias' => $categorias,
            'parentCategorias' => $parentCategorias,
        ]);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Gestión de Categorías y Ámbitos') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Organiza las categorías en ámbitos (Cristiano vs General/Secular) y define subcategorías.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nueva Categoría') }}
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
                placeholder="Buscar categoría por nombre o slug..."
                clearable
            />
        </div>

        <div class="w-full sm:w-56">
            <flux:select wire:model.live="ambitoFilter" placeholder="Todos los Ámbitos">
                <flux:select.option value="">{{ __('Todos los Ámbitos') }}</flux:select.option>
                <flux:select.option value="cristiano">⛪ {{ __('Cristiano') }}</flux:select.option>
                <flux:select.option value="secular">🎸 {{ __('General / Secular') }}</flux:select.option>
                <flux:select.option value="ambos">🎶 {{ __('Ambos Ámbitos') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tabla de Categorías -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="first:!ps-6">{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Categoría Padre') }}</flux:table.column>
                <flux:table.column>{{ __('Ámbito') }}</flux:table.column>
                <flux:table.column>{{ __('Canciones') }}</flux:table.column>
                <flux:table.column align="center" class="last:!pe-6">{{ __('Acciones') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categorias as $cat)
                    <flux:table.row wire:key="categoria-{{ $cat->id }}">
                        <flux:table.cell class="first:!ps-6 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                    <flux:icon.tag variant="outline" class="size-5" />
                                </span>
                                <div>
                                    <div class="font-semibold">{{ $cat->nombre }}</div>
                                    <div class="text-xs text-zinc-500 font-mono">{{ $cat->slug }}</div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-xs">
                            @if ($cat->parent)
                                <flux:badge color="zinc" size="sm">{{ $cat->parent->nombre }}</flux:badge>
                            @else
                                <span class="text-zinc-400 italic">{{ __('Principal (Raíz)') }}</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $badgeColor = match($cat->ambito) {
                                    'cristiano' => 'purple',
                                    'secular' => 'amber',
                                    default => 'sky',
                                };
                                $label = match($cat->ambito) {
                                    'cristiano' => '⛪ Cristiano',
                                    'secular' => '🎸 General / Secular',
                                    default => '🎶 Ambos',
                                };
                            @endphp
                            <flux:badge color="{{ $badgeColor }}" size="sm">{{ $label }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="purple" size="sm">
                                {{ $cat->canciones_count }} {{ __('canciones') }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="last:!pe-6">
                            <div class="flex items-center justify-center gap-2">
                                <flux:button
                                    variant="ghost"
                                    icon="pencil-square"
                                    size="sm"
                                    wire:click="openEditModal({{ $cat->id }})"
                                    title="Editar Categoría"
                                />

                                <flux:button
                                    variant="ghost"
                                    icon="trash"
                                    size="sm"
                                    class="text-red-500 hover:text-red-700"
                                    wire:click="confirmDelete({{ $cat->id }})"
                                    title="Eliminar Categoría"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                            {{ __('No se encontraron categorías registradas.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $categorias->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Categoría -->
    <flux:modal wire:model="showCategoryModal" name="category-modal" class="max-w-md">
        <form wire:submit="saveCategory" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingCategoryId ? __('Editar Categoría') : __('Crear Nueva Categoría') }}
                </flux:heading>
                <flux:text variant="subtle">
                    {{ $editingCategoryId ? __('Actualiza la información de la categoría.') : __('Ingresa los datos para clasificar las canciones.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input
                    wire:model.live.debounce.300ms="nombre"
                    label="Nombre de la Categoría"
                    placeholder="Ej: Adoración, Rock, Baladas"
                    required
                />

                <flux:input
                    wire:model="slug"
                    label="Slug (URL Identificador)"
                    placeholder="ej-adoracion"
                    required
                />

                <flux:select wire:model="parent_id" label="Categoría Padre (Opcional)">
                    <flux:select.option value="">{{ __('Ninguna (Categoría Principal)') }}</flux:select.option>
                    @foreach ($parentCategorias as $pCat)
                        <flux:select.option value="{{ $pCat->id }}">{{ $pCat->nombre }} ({{ Str::headline($pCat->ambito) }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="ambito" label="Ámbito de la Categoría" required>
                    <flux:select.option value="cristiano">⛪ {{ __('Cristiano (Alabanza y Adoración)') }}</flux:select.option>
                    <flux:select.option value="secular">🎸 {{ __('General / Secular (Eventos, Bares, Matrimonios)') }}</flux:select.option>
                    <flux:select.option value="ambos">🎶 {{ __('Ambos Ámbitos (Híbrido)') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showCategoryModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingCategoryId ? __('Guardar Cambios') : __('Crear Categoría') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Confirmar Eliminación -->
    <flux:modal wire:model="showDeleteModal" name="delete-category-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400 flex items-center gap-2">
                    <flux:icon.exclamation-triangle variant="outline" />
                    {{ __('Eliminar Categoría') }}
                </flux:heading>
                <flux:text variant="subtle" class="mt-2">
                    ¿Estás seguro de que deseas eliminar la categoría <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingCategoryName }}</strong>?
                    @if ($deletingCategorySongsCount > 0)
                        <span class="block mt-2 text-amber-600 dark:text-amber-400 font-semibold">
                            ⚠️ Hay {{ $deletingCategorySongsCount }} canción(es) asociadas. Al eliminar la categoría, las canciones pasarán a estar sin categoría.
                        </span>
                    @endif
                </flux:text>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteCategory">
                    {{ __('Eliminar Categoría') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
