<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Layout('components.layouts.app.sidebar')] #[Title('Gestión de Usuarios')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    // Modal state
    public bool $showUserModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $selectedRoles = [];

    public ?int $deletingUserId = null;
    public string $deletingUserName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selectedRoles = [];
        $this->showUserModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $this->resetErrorBag();
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showUserModal = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'selectedRoles' => ['array'],
        ];

        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $validated = $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->syncRoles($validated['selectedRoles']);

            session()->flash('status', 'Usuario actualizado exitosamente.');
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->syncRoles($validated['selectedRoles']);

            session()->flash('status', 'Usuario creado exitosamente.');
        }

        $this->showUserModal = false;
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return;
        }

        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        if ($this->deletingUserId === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            $this->showDeleteModal = false;
            return;
        }

        $user = User::findOrFail($this->deletingUserId);
        $user->delete();

        $this->deletingUserId = null;
        $this->deletingUserName = '';
        $this->showDeleteModal = false;

        session()->flash('status', 'Usuario eliminado correctamente.');
    }

    public function render(): mixed
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', fn ($r) => $r->where('name', $this->roleFilter));
            })
            ->latest()
            ->paginate(10);

        $roles = Role::all();

        return view('pages.admin.users.⚡index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Gestión de Usuarios') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Administra las cuentas de usuario y sus roles asignados en el sistema.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nuevo Usuario') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" heading="{{ session('error') }}" />
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Buscar por nombre o correo..."
                clearable
            />
        </div>

        <div class="w-full sm:w-64">
            <flux:select wire:model.live="roleFilter" placeholder="Todos los roles">
                <flux:select.option value="">{{ __('Todos los roles') }}</flux:select.option>
                @foreach ($roles as $role)
                    <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="first:!ps-6">{{ __('Usuario') }}</flux:table.column>
                <flux:table.column>{{ __('Correo Electrónico') }}</flux:table.column>
                <flux:table.column>{{ __('Roles') }}</flux:table.column>
                <flux:table.column>{{ __('Fecha Registro') }}</flux:table.column>
                <flux:table.column align="center" class="last:!pe-6">{{ __('Acciones') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="first:!ps-6 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-100 font-semibold text-xs">
                                    {{ $user->initials() }}
                                </span>
                                <div>
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    @if ($user->id === auth()->id())
                                        <flux:badge color="blue" size="sm" class="mt-0.5">{{ __('Tú') }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>{{ $user->email }}</flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    @php
                                        $badgeColor = match($role->name) {
                                            'SuperAdministrador' => 'purple',
                                            'Administrador de Banda' => 'amber',
                                            'Integrante de Banda' => 'sky',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge color="{{ $badgeColor }}" size="sm">{{ $role->name }}</flux:badge>
                                @empty
                                    <flux:text variant="subtle" class="text-xs">{{ __('Sin Rol') }}</flux:text>
                                @endforelse
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-zinc-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </flux:table.cell>

                        <flux:table.cell align="center" class="last:!pe-6">
                            <div class="flex items-center justify-center gap-2">
                                <flux:button
                                    variant="ghost"
                                    icon="pencil-square"
                                    size="sm"
                                    wire:click="openEditModal({{ $user->id }})"
                                    title="Editar Usuario"
                                />

                                @if ($user->id !== auth()->id())
                                    <flux:button
                                        variant="ghost"
                                        icon="trash"
                                        size="sm"
                                        class="text-red-500 hover:text-red-700"
                                        wire:click="confirmDelete({{ $user->id }})"
                                        title="Eliminar Usuario"
                                    />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                            {{ __('No se encontraron usuarios que coincidan con la búsqueda.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Crear/Editar Usuario -->
    <flux:modal wire:model="showUserModal" name="user-modal" class="max-w-md">
        <form wire:submit="saveUser" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingUserId ? __('Editar Usuario') : __('Crear Nuevo Usuario') }}
                </flux:heading>
                <flux:text variant="subtle">
                    {{ $editingUserId ? __('Actualiza los datos del usuario y sus roles.') : __('Ingresa la información para dar de alta un nuevo usuario.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nombre Completo"
                    required
                />

                <flux:input
                    wire:model="email"
                    type="email"
                    label="Correo Electrónico"
                    required
                />

                <flux:input
                    wire:model="password"
                    type="password"
                    label="Contraseña"
                    :placeholder="$editingUserId ? __('Dejar en blanco para mantener la actual') : __('Mínimo 8 caracteres')"
                    :required="!$editingUserId"
                />

                <div class="space-y-2">
                    <flux:label>{{ __('Roles Asignados') }}</flux:label>
                    <div class="space-y-2 border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                        @foreach ($roles as $role)
                            <flux:checkbox
                                wire:model="selectedRoles"
                                value="{{ $role->name }}"
                                label="{{ $role->name }}"
                            />
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showUserModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingUserId ? __('Guardar Cambios') : __('Crear Usuario') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Confirmar Eliminación -->
    <flux:modal wire:model="showDeleteModal" name="delete-user-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400 flex items-center gap-2">
                    <flux:icon.exclamation-triangle variant="outline" />
                    {{ __('Eliminar Usuario') }}
                </flux:heading>
                <flux:text variant="subtle">
                    ¿Estás seguro de que deseas eliminar al usuario <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingUserName }}</strong>? Esta acción no se puede deshacer.
                </flux:text>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteUser">
                    {{ __('Eliminar Usuario') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
