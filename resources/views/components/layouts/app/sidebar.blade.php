<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand :href="route('admin')" :current="request()->routeIs('admin')" wire:navigate class="px-2">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-zinc-900 dark:bg-zinc-800 text-white p-1 shrink-0 shadow-sm border border-zinc-700/50">
                        <x-app-logo-icon class="size-6 text-white" />
                    </div>
                    <div class="flex items-center font-sans tracking-tight in-data-flux-sidebar-collapsed-desktop:hidden">
                        <span class="text-base font-extrabold text-zinc-900 dark:text-white leading-none">quecantamos</span><span class="text-base font-black text-amber-500 leading-none">.cl</span>
                    </div>
                </div>
            </flux:sidebar.brand>

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <!-- Dashboard -->
            <flux:sidebar.item icon="home" :href="route('admin')" :current="request()->routeIs('admin')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>

            <!-- Repertorio Público Global -->
            <flux:sidebar.item icon="musical-note" :href="route('repertorio')"
                :current="request()->routeIs('repertorio')" wire:navigate>{{ __('Repertorio Público') }}</flux:sidebar.item>

            <!-- Bandas y Setlists -->
            @php
                $userBandas = auth()->user()->hasRole('SuperAdministrador')
                    ? \App\Models\Banda::all()
                    : auth()->user()->bandas;
                $activeBandaId = session('active_banda_id') ?? $userBandas->first()?->id;
                $activeBanda = $userBandas->firstWhere('id', $activeBandaId);
            @endphp

            <flux:sidebar.item icon="user-group" :href="route('bandas.index')" :current="request()->routeIs('bandas.index')" wire:navigate>
                {{ __('Mis Bandas') }}
            </flux:sidebar.item>

            @if ($activeBanda)
                <flux:navlist.group heading="{{ $activeBanda->nombre }}" expandable icon="star" :expanded="request()->routeIs('bandas.show') || request()->routeIs('bandas.repertorio.*') || request()->routeIs('bandas.setlists.*') || request()->routeIs('bandas.miembros')">
                    <flux:navlist.item icon="home" :href="route('bandas.show', $activeBanda->slug)" :current="request()->routeIs('bandas.show')" wire:navigate>
                        {{ __('Inicio Banda') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="queue-list" :href="route('bandas.setlists.index', $activeBanda->slug)" :current="request()->routeIs('bandas.setlists.*')" wire:navigate>
                        {{ __('Setlists') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="musical-note" :href="route('bandas.repertorio.index', $activeBanda->slug)" :current="request()->routeIs('bandas.repertorio.*')" wire:navigate>
                        {{ __('Repertorio Banda') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('bandas.miembros', $activeBanda->slug)" :current="request()->routeIs('bandas.miembros')" wire:navigate>
                        {{ __('Miembros') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="cog-6-tooth" :href="route('bandas.configuracion', $activeBanda->slug)" :current="request()->routeIs('bandas.configuracion')" wire:navigate>
                        {{ __('Configuración') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endif

            @can('gestionar-usuarios')
                <flux:navlist.group heading="Administración" expandable icon="shield-check" :expanded="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.categorias.*') || request()->routeIs('admin.bandas.*')">
                    <flux:navlist.item icon="user-group" :href="route('admin.bandas.index')"
                        :current="request()->routeIs('admin.bandas.*')" wire:navigate>{{ __('Bandas Globales') }}</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.users.index')"
                        :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                    <flux:navlist.item icon="key" :href="route('admin.roles.index')"
                        :current="request()->routeIs('admin.roles.*')" wire:navigate>{{ __('Roles y Permisos') }}</flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="route('admin.categorias.index')"
                        :current="request()->routeIs('admin.categorias.*')" wire:navigate>{{ __('Categorías') }}</flux:navlist.item>
                </flux:navlist.group>
            @endcan
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item x-show="$flux.appearance === 'dark'" icon="sun" x-on:click="$flux.appearance = 'light'"
                class="cursor-pointer">{{ __('Modo Claro') }}
            </flux:sidebar.item>
            <flux:sidebar.item x-show="$flux.appearance !== 'dark'" icon="moon" x-on:click="$flux.appearance = 'dark'"
                class="cursor-pointer">{{ __('Modo Oscuro') }}
            </flux:sidebar.item>


        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile :name="auth()->user()->name" :initials="auth()->user()->initials()" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    <flux:toast position="bottom end" />
    @fluxScripts
</body>

</html>