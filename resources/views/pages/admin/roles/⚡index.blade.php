<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('components.layouts.app.sidebar')] #[Title('Gestión de Roles y Permisos')] class extends Component {
    // Modal states
    public bool $showRoleModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingRoleId = null;
    public string $name = '';
    public array $selectedPermissions = [];

    public ?int $deletingRoleId = null;
    public string $deletingRoleName = '';

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingRoleId = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->showRoleModal = true;
    }

    public function openEditModal(int $roleId): void
    {
        $this->resetErrorBag();
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,' . $this->editingRoleId,
            ],
            'selectedPermissions' => ['array'],
        ]);

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);

            // Evitar renombrar SuperAdministrador si es este rol
            if ($role->name === 'SuperAdministrador' && $validated['name'] !== 'SuperAdministrador') {
                session()->flash('error', 'El nombre del rol SuperAdministrador no se puede cambiar.');
                return;
            }

            $role->name = $validated['name'];
            $role->save();
            $role->syncPermissions($validated['selectedPermissions']);

            session()->flash('status', 'Rol y permisos actualizados exitosamente.');
        } else {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($validated['selectedPermissions']);

            session()->flash('status', 'Nuevo rol creado exitosamente.');
        }

        $this->showRoleModal = false;
    }

    public function confirmDelete(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'SuperAdministrador') {
            session()->flash('error', 'No se puede eliminar el rol SuperAdministrador.');
            return;
        }

        $this->deletingRoleId = $role->id;
        $this->deletingRoleName = $role->name;
        $this->showDeleteModal = true;
    }

    public function deleteRole(): void
    {
        if (! $this->deletingRoleId) {
            return;
        }

        $role = Role::findOrFail($this->deletingRoleId);

        if ($role->name === 'SuperAdministrador') {
            session()->flash('error', 'No se puede eliminar el rol SuperAdministrador.');
            $this->showDeleteModal = false;
            return;
        }

        $role->delete();

        $this->deletingRoleId = null;
        $this->deletingRoleName = '';
        $this->showDeleteModal = false;

        session()->flash('status', 'Rol eliminado correctamente.');
    }

    public function render(): mixed
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $allPermissions = Permission::all();

        return view('pages.admin.roles.⚡index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
        ]);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Gestión de Roles y Permisos') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Administra los roles de usuario y asigna los permisos correspondientes.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Nuevo Rol') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" heading="{{ session('error') }}" />
    @endif

    <!-- Grilla de Roles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($roles as $role)
            <div wire:key="role-card-{{ $role->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg" level="2">{{ $role->name }}</flux:heading>

                        @php
                            $badgeColor = match($role->name) {
                                'SuperAdministrador' => 'purple',
                                'Administrador de Banda' => 'amber',
                                'Integrante de Banda' => 'sky',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge color="{{ $badgeColor }}" size="sm">
                            {{ $role->users->count() }} {{ trans_choice('usuario|usuarios', $role->users->count()) }}
                        </flux:badge>
                    </div>

                    <div class="space-y-1">
                        <flux:text variant="subtle" class="text-xs uppercase tracking-wider font-semibold">
                            {{ __('Permisos Asignados') }}
                        </flux:text>

                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @forelse ($role->permissions as $permission)
                                <flux:badge color="zinc" variant="solid" size="sm" class="text-xs">
                                    {{ $permission->name }}
                                </flux:badge>
                            @empty
                                <flux:text variant="subtle" class="text-xs italic">{{ __('Sin permisos específicos asignados') }}</flux:text>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                    <flux:button
                        variant="outline"
                        icon="pencil-square"
                        size="sm"
                        wire:click="openEditModal({{ $role->id }})"
                    >
                        {{ __('Editar Permisos') }}
                    </flux:button>

                    @if ($role->name !== 'SuperAdministrador')
                        <flux:button
                            variant="ghost"
                            icon="trash"
                            size="sm"
                            class="text-red-500 hover:text-red-700"
                            wire:click="confirmDelete({{ $role->id }})"
                        />
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Crear/Editar Rol -->
    <flux:modal wire:model="showRoleModal" name="role-modal" class="max-w-lg">
        <form wire:submit="saveRole" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingRoleId ? __('Editar Rol y Permisos') : __('Crear Nuevo Rol') }}
                </flux:heading>
                <flux:text variant="subtle">
                    {{ $editingRoleId ? __('Configura los permisos asociados al rol.') : __('Define un nombre y selecciona los permisos para el nuevo rol.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nombre del Rol"
                    :disabled="$editingRoleId && $name === 'SuperAdministrador'"
                    required
                />

                <div class="space-y-2">
                    <flux:label>{{ __('Permisos del Sistema') }}</flux:label>
                    <div class="space-y-2.5 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 bg-zinc-50 dark:bg-zinc-800/50">
                        @foreach ($allPermissions as $permission)
                            <flux:checkbox
                                wire:model="selectedPermissions"
                                value="{{ $permission->name }}"
                                label="{{ $permission->name }}"
                                :disabled="$editingRoleId && $name === 'SuperAdministrador'"
                            />
                        @endforeach
                    </div>
                    @if ($editingRoleId && $name === 'SuperAdministrador')
                        <flux:text variant="subtle" class="text-xs">
                            * {{ __('El rol SuperAdministrador tiene acceso implícito a todos los permisos mediante Gate::before.') }}
                        </flux:text>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showRoleModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingRoleId ? __('Guardar Cambios') : __('Crear Rol') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Confirmar Eliminación -->
    <flux:modal wire:model="showDeleteModal" name="delete-role-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400 flex items-center gap-2">
                    <flux:icon.exclamation-triangle variant="outline" />
                    {{ __('Eliminar Rol') }}
                </flux:heading>
                <flux:text variant="subtle">
                    ¿Estás seguro de que deseas eliminar el rol <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingRoleName }}</strong>?
                </flux:text>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteRole">
                    {{ __('Eliminar Rol') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
