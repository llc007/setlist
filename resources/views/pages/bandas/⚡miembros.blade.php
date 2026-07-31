<?php

use App\Models\Banda;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app.sidebar')] class extends Component {
    public Banda $banda;

    public bool $showAddMemberModal = false;
    public ?int $selectedUserId = null;
    public string $rol = 'Integrante de Banda';

    public function mount(Banda $banda): void
    {
        $this->banda = $banda;
        session()->put('active_banda_id', $this->banda->id);
    }

    public function openAddMemberModal(): void
    {
        $this->resetErrorBag();
        $this->selectedUserId = null;
        $this->rol = 'Integrante de Banda';
        $this->showAddMemberModal = true;
    }

    public function addMember(): void
    {
        $this->validate([
            'selectedUserId' => ['required', 'exists:users,id'],
            'rol' => ['required', 'string', 'in:Administrador de Banda,Integrante de Banda'],
        ]);

        if (! $this->banda->miembros()->where('users.id', $this->selectedUserId)->exists()) {
            $this->banda->miembros()->attach($this->selectedUserId, [
                'rol' => $this->rol,
            ]);

            session()->flash('status', 'Miembro agregado a la banda.');
        }

        $this->showAddMemberModal = false;
    }

    public function removeMember(int $userId): void
    {
        $this->banda->miembros()->detach($userId);
        session()->flash('status', 'Miembro removido de la banda.');
    }

    public function updateRole(int $userId, string $newRole): void
    {
        $this->banda->miembros()->updateExistingPivot($userId, [
            'rol' => $newRole,
        ]);

        session()->flash('status', 'Rol del miembro actualizado.');
    }

    public function render(): mixed
    {
        $miembros = $this->banda->miembros()->get();
        $existingMemberIds = $miembros->pluck('id')->toArray();

        // Usuarios disponibles para agregar
        $availableUsers = User::whereNotIn('id', $existingMemberIds)
            ->orderBy('name')
            ->get();

        return view('pages.bandas.⚡miembros', [
            'miembros' => $miembros,
            'availableUsers' => $availableUsers,
        ])->title('Miembros - ' . $this->banda->nombre);
    }
};
?>

<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Miembros de ') . $this->banda->nombre }}</flux:heading>
            <flux:text variant="subtle">{{ __('Administra los integrantes y asigna roles dentro de la banda.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="openAddMemberModal">
            {{ __('Agregar Miembro') }}
        </flux:button>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('status'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
    @endif

    <!-- Tabla de Miembros -->
    <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="first:!ps-6">{{ __('Integrante') }}</flux:table.column>
                <flux:table.column>{{ __('Correo Electrónico') }}</flux:table.column>
                <flux:table.column>{{ __('Rol en la Banda') }}</flux:table.column>
                <flux:table.column align="center" class="last:!pe-6">{{ __('Acciones') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($miembros as $miembro)
                    <flux:table.row wire:key="miembro-{{ $miembro->id }}">
                        <flux:table.cell class="first:!ps-6 font-medium text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-100 font-semibold text-xs">
                                    {{ $miembro->initials() }}
                                </span>
                                <div>
                                    <div class="font-semibold">{{ $miembro->name }}</div>
                                    @if ($miembro->id === auth()->id())
                                        <flux:badge color="blue" size="sm" class="mt-0.5">{{ __('Tú') }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>{{ $miembro->email }}</flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $miembro->pivot->rol === 'Administrador de Banda' ? 'purple' : 'sky' }}" size="sm">
                                {{ $miembro->pivot->rol }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="center" class="last:!pe-6">
                            <div class="flex items-center justify-center gap-2">
                                @if ($miembro->id !== auth()->id())
                                    <flux:button
                                        variant="ghost"
                                        icon="trash"
                                        size="sm"
                                        class="text-red-500 hover:text-red-700"
                                        wire:click="removeMember({{ $miembro->id }})"
                                        title="Quitar de la Banda"
                                    />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-8 text-zinc-500">
                            {{ __('No hay integrantes registrados en esta banda.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Modal Agregar Miembro -->
    <flux:modal wire:model="showAddMemberModal" name="add-member-modal" class="max-w-md">
        <form wire:submit="addMember" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Agregar Integrante a la Banda') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Selecciona un usuario del sistema para añadirlo a ' . $banda->nombre) }}</flux:text>
            </div>

            <div class="space-y-4">
                <flux:select wire:model="selectedUserId" label="Usuario Registrado" required>
                    <flux:select.option value="">{{ __('Selecciona un usuario...') }}</flux:select.option>
                    @foreach ($availableUsers as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="rol" label="Rol en la Banda">
                    <flux:select.option value="Integrante de Banda">{{ __('Integrante de Banda') }}</flux:select.option>
                    <flux:select.option value="Administrador de Banda">{{ __('Administrador de Banda') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showAddMemberModal', false)">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Agregar Miembro') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
