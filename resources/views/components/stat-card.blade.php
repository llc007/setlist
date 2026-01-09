@props(['title', 'value', 'icon', 'trend' => null, 'route' => '#', 'trendColor' => 'emerald'])

<a href="{{ $route }}" wire:navigate
    class="block flex flex-col gap-3 rounded-xl p-5 bg-white dark:bg-card-dark 
    shadow-sm border border-slate-200 dark:border-slate-800 transition-all hover:-translate-y-1 duration-300
    cursor-pointer no-underline group">

    <div class="flex items-center justify-between">
        <p class="text-slate-500 dark:text-text-secondary text-sm font-medium uppercase tracking-wider">
            {{ $title }}
        </p>
        <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">
            {{ $icon }}
        </span>
    </div>

    <div class="flex items-end gap-3">
        <p class="text-slate-900 dark:text-white text-3xl font-bold leading-none">
            {{ $value }}
        </p>
        @if ($trend)
            <p class="text-{{ $trendColor }}-500 text-sm font-bold bg-{{ $trendColor }}-500/10 px-2 py-0.5 rounded">
                {{ $trend }}
            </p>
        @endif
    </div>
</a>
