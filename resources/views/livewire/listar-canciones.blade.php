<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Biblioteca de Canciones</flux:heading>

        <div class="w-1/3">
            <flux:input wire:model.live.debounce.300ms="buscar" icon="magnifying-glass"
                placeholder="Buscar por título o artista..." />
        </div>
    </div>

    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Canción
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Categoría
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Tono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Recursos
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($canciones as $cancion)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $cancion->titulo }}</div>
                            <div class="text-sm text-zinc-500">{{ $cancion->artista }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <flux:badge size="sm" inset="top bottom" color="zinc">
                                {{ $cancion->categoria->nombre ?? 'General' }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-primary-500">
                            {{ $cancion->tono_original }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-3">
                                @foreach ($cancion->recursos as $recurso)
                                    <a href="{{ $recurso->url }}" target="_blank"
                                        class="text-zinc-400 hover:text-primary-500 transition-colors">
                                        @if ($recurso->tipo === 'pdf')
                                            <flux:icon.document-text variant="outline" class="size-5" />
                                        @elseif($recurso->tipo === 'youtube')
                                            <flux:icon.video-camera variant="outline" class="size-5" />
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-zinc-500">
                            No se encontraron canciones con "{{ $buscar }}"
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $canciones->links() }}
    </div>
</div>
