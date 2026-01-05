<x-layouts.app :title="__('Admin Dashboard')">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header Section --}}
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" level="1">Welcome back, {{ auth()->user()->name }}</flux:heading>
            <div class="text-zinc-500 dark:text-zinc-400 text-lg">Here's what's happening this Sunday, Oct 24</div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="flex flex-col gap-2 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text weight="medium">TOTAL SONGS</flux:text>
                    <flux:icon.musical-note variant="outline" class="size-5 text-blue-500" />
                </div>
                <div class="flex items-end gap-2">
                    <flux:heading size="xl">342</flux:heading>
                    <flux:badge color="green" size="sm" inset="top">+12%</flux:badge>
                </div>
            </div>

            <div class="flex flex-col gap-2 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text weight="medium">ACTIVE MEMBERS</flux:text>
                    <flux:icon.users variant="outline" class="size-5 text-blue-500" />
                </div>
                <div class="flex items-end gap-2">
                    <flux:heading size="xl">45</flux:heading>
                    <flux:badge color="green" size="sm" inset="top">+2%</flux:badge>
                </div>
            </div>

            <div class="flex flex-col gap-2 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text weight="medium">SERVICES THIS MONTH</flux:text>
                    <flux:icon.calendar variant="outline" class="size-5 text-blue-500" />
                </div>
                <div class="flex items-end gap-2">
                    <flux:heading size="xl">8</flux:heading>
                    <flux:text size="sm">On track</flux:text>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left column (Actions + Services + Songs) --}}
            <div class="flex flex-col gap-6 lg:col-span-2">
                {{-- Quick Actions --}}
                <div class="flex flex-col gap-3">
                    <flux:heading level="2" size="sm" weight="semibold" class="uppercase text-zinc-400">Quick Actions</flux:heading>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <flux:button variant="primary" icon="plus" class="justify-center">Create New Setlist</flux:button>
                        <flux:button variant="filled" icon="plus-circle" class="justify-center">Add New Song</flux:button>
                        <flux:button variant="filled" icon="envelope" class="justify-center">Message Choir</flux:button>
                    </div>
                </div>

                {{-- Upcoming Services --}}
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <flux:heading level="2" size="sm" weight="semibold" class="uppercase text-zinc-400">Upcoming Services</flux:heading>
                        <flux:button variant="ghost" size="sm" class="text-blue-500">View Calendar</flux:button>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                            <div class="flex flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 p-2 text-center min-w-[60px]">
                                <flux:text size="xs" class="uppercase">Oct</flux:text>
                                <flux:heading size="lg">24</flux:heading>
                                <flux:text size="xs">Sun</flux:text>
                            </div>
                            <div class="flex-1 flex flex-col gap-1">
                                <flux:heading level="3" size="md">Sunday Morning Worship</flux:heading>
                                <div class="flex items-center gap-4">
                                    <div class="h-1.5 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full w-[80%] bg-blue-500"></div>
                                    </div>
                                    <flux:text size="xs">80% Complete</flux:text>
                                </div>
                                <flux:text size="xs">4/5 Songs Selected</flux:text>
                            </div>
                            <flux:button variant="ghost" size="sm" icon="pencil-square" />
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 border-l-4 border-l-green-500">
                            <div class="flex flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 p-2 text-center min-w-[60px]">
                                <flux:text size="xs" class="uppercase">Oct</flux:text>
                                <flux:heading size="lg">27</flux:heading>
                                <flux:text size="xs">Wed</flux:text>
                            </div>
                            <div class="flex-1 flex flex-col gap-1">
                                <flux:heading level="3" size="md">Mid-week Practice</flux:heading>
                                <div class="flex items-center gap-4">
                                    <div class="h-1.5 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                        <div class="h-full w-full bg-green-500"></div>
                                    </div>
                                    <flux:badge color="green" size="sm">Ready</flux:badge>
                                </div>
                                <flux:text size="xs">2/2 Songs Selected</flux:text>
                            </div>
                            <flux:button variant="ghost" size="sm" icon="pencil-square" />
                        </div>
                    </div>
                </div>

                {{-- Recent Songs --}}
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <flux:heading level="2" size="sm" weight="semibold" class="uppercase text-zinc-400">Recent Songs</flux:heading>
                        <flux:button variant="ghost" size="sm" class="text-blue-500">View Library</flux:button>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-4 p-2 pl-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                             <div class="h-10 w-10 overflow-hidden rounded-lg">
                                <x-placeholder-pattern class="size-full stroke-blue-500/20" />
                            </div>
                            <div class="flex-1">
                                <flux:heading size="sm">Oceans (Where Feet May Fail)</flux:heading>
                                <flux:text size="xs">Hillsong United • Key of D</flux:text>
                            </div>
                            <flux:badge size="sm">Contemporary</flux:badge>
                        </div>
                        <div class="flex items-center gap-4 p-2 pl-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                             <div class="h-10 w-10 overflow-hidden rounded-lg bg-orange-500">
                                <x-placeholder-pattern class="size-full stroke-white/20" />
                            </div>
                            <div class="flex-1">
                                <flux:heading size="sm">Amazing Grace</flux:heading>
                                <flux:text size="xs">Traditional • Key of G</flux:text>
                            </div>
                            <flux:badge size="sm">Hymn</flux:badge>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column (Notifications + Tip) --}}
            <div class="flex flex-col gap-6">
                {{-- Notifications --}}
                <div class="flex flex-col gap-4 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:heading level="2" size="md" weight="semibold">Notifications</flux:heading>
                        <flux:badge color="red" size="sm" class="px-2">2</flux:badge>
                    </div>
                    <div class="flex flex-col gap-6">
                        <div class="flex gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-orange-500/10 text-orange-500">
                                <flux:icon.user-plus variant="mini" class="size-4" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <flux:text size="sm" weight="medium">Member Access Request</flux:text>
                                <flux:text size="xs">3 Choir members requested access to the portal.</flux:text>
                                <div class="flex gap-2 pt-1">
                                    <flux:button variant="ghost" size="xs" class="text-blue-500 p-0">Review</flux:button>
                                    <flux:button variant="ghost" size="xs" class="p-0">Dismiss</flux:button>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 text-zinc-400">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-blue-500">
                                <flux:icon.clock variant="mini" class="size-4" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <flux:text size="sm" weight="medium">Schedule Update</flux:text>
                                <flux:text size="xs">Rehearsal time changed for Nov 1 to 7:00 PM.</flux:text>
                            </div>
                        </div>

                        <div class="flex gap-3 text-zinc-400">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-green-500/10 text-green-500">
                                <flux:icon.check-circle variant="mini" class="size-4" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <flux:text size="sm" weight="medium">Setlist Approved</flux:text>
                                <flux:text size="xs">Pastor Mark approved the setlist for Oct 24.</flux:text>
                                <flux:text size="xs" class="text-zinc-500">2 hours ago</flux:text>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tip of the day --}}
                <div class="flex flex-col gap-3 bg-blue-500/5 p-4 rounded-xl border border-blue-500/20">
                    <div class="flex items-center gap-2 text-blue-500">
                        <flux:icon.light-bulb variant="mini" class="size-4" />
                        <flux:text size="sm" weight="semibold" class="text-blue-500 uppercase">Tip of the day</flux:text>
                    </div>
                    <div class="flex flex-col gap-2">
                        <flux:heading size="sm">Transposing charts?</flux:heading>
                        <flux:text size="sm">Use the "Smart Transpose" feature in the Songs tab to automatically update chord charts for all instruments.</flux:text>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
