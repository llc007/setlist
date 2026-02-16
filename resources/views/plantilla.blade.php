<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Detalles y Recursos de la Canción - WorshipPlanner</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#111418",
                        "surface-dark": "#1c2128",
                        "surface-light": "#ffffff",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "mono": ["ui-monospace", "SFMono-Regular", "Menlo", "Monaco", "Consolas", "Liberation Mono", "Courier New", "monospace"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .material-symbols-filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark ::-webkit-scrollbar-track {
            background: #111418;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #283039;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #3b4754;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased selection:bg-primary/30 selection:text-primary overflow-hidden transition-colors duration-200">
    <div class="flex h-screen w-full">
        <aside
            class="hidden lg:flex w-72 flex-col border-r border-gray-200 dark:border-[#283039] bg-white dark:bg-[#111418] transition-colors duration-200">
            <div class="flex h-full flex-col justify-between p-4">
                <div class="flex flex-col gap-4">
                    <div class="flex gap-3 items-center px-2 py-2">
                        <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 bg-gray-200 dark:bg-gray-700"
                            data-alt="User profile abstract gradient avatar"
                            style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDOtZmBmFxYpPH4AKFiOGX4EuyxBAY-hV-gqNLMa8qa77aBsul0GsQR4pwNtnEhUXjx38AtXUsm0SuoLdqQK7YdMIO8FPoAuUJo6zV2-NXWloeb3xv4uEHzdwB3RUjEe6iTowmWBNrVE4eLUtb3Tr3IN07KXJR3jypmtSbXeflfshYfRQekUiStREa8TLGds__fZ9vgceCVhY7_m6zWG_9lP-Ivy-9dX_EJE1j66cIvBvL-es0lSyfIxwBffsYrzQ65ZwK_6EUmPvNr");'>
                        </div>
                        <div class="flex flex-col">
                            <h1 class="text-slate-900 dark:text-white text-base font-bold leading-normal">WorshipPlanner
                            </h1>
                            <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-normal leading-normal">Admin</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 mt-4">
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors"
                            href="#">
                            <span class="material-symbols-outlined">dashboard</span>
                            <p class="text-sm font-medium leading-normal">Inicio</p>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors"
                            href="#">
                            <span class="material-symbols-outlined">queue_music</span>
                            <p class="text-sm font-medium leading-normal">Listas</p>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 dark:bg-primary/20 text-primary border border-primary/20"
                            href="#">
                            <span class="material-symbols-outlined material-symbols-filled">music_note</span>
                            <p class="text-sm font-bold leading-normal">Canciones</p>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors"
                            href="#">
                            <span class="material-symbols-outlined">library_music</span>
                            <p class="text-sm font-medium leading-normal">Biblioteca</p>
                        </a>
                        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors"
                            href="#">
                            <span class="material-symbols-outlined">group</span>
                            <p class="text-sm font-medium leading-normal">Equipo</p>
                        </a>
                    </div>
                </div>
                <div class="px-3">
                    <button
                        class="flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors mb-2">
                        <span class="material-symbols-outlined">dark_mode</span>
                        <p class="text-sm font-medium leading-normal">Cambiar Tema</p>
                    </button>
                    <button
                        class="flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#283039] transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <p class="text-sm font-medium leading-normal">Cerrar Sesión</p>
                    </button>
                </div>
            </div>
        </aside>
        <main
            class="flex-1 flex flex-col h-full relative overflow-y-auto w-full bg-background-light dark:bg-background-dark transition-colors duration-200">
            <div
                class="lg:hidden flex items-center justify-between p-4 border-b border-gray-200 dark:border-[#283039] bg-white dark:bg-[#111418]">
                <div class="flex items-center gap-2">
                    <button class="text-slate-900 dark:text-white" type="button">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="font-bold text-slate-900 dark:text-white">WorshipPlanner</span>
                </div>
            </div>
            <div class="w-full max-w-[1400px] mx-auto p-4 lg:p-8 flex flex-col gap-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm">
                        <a class="text-slate-500 dark:text-[#9dabb9] hover:text-primary transition-colors font-medium"
                            href="#">Canciones</a>
                        <span
                            class="text-slate-400 dark:text-[#9dabb9] material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-slate-900 dark:text-white font-medium">Amazing Grace (My Chains Are
                            Gone)</span>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm"
                            type="button">
                            <span class="material-symbols-outlined text-[18px]">ios_share</span>
                            <span class="hidden sm:inline">Compartir</span>
                        </button>
                        <button
                            class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-white dark:bg-[#283039] border border-gray-200 dark:border-transparent text-slate-700 dark:text-white text-xs font-bold hover:bg-gray-50 dark:hover:bg-[#3b4754] transition-colors shadow-sm"
                            type="button">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                            <span class="hidden sm:inline">Imprimir</span>
                        </button>
                        <button
                            class="flex items-center justify-center gap-2 rounded-lg h-9 px-4 bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20"
                            type="button">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            <span>Editar</span>
                        </button>
                    </div>
                </div>
                <div
                    class="flex flex-wrap justify-between items-end gap-6 pb-6 border-b border-gray-200 dark:border-[#283039]">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black tracking-tight">
                            Amazing Grace (My Chains Are Gone)</h1>
                        <div class="flex items-center gap-2 text-slate-500 dark:text-[#9dabb9]">
                            <span class="material-symbols-outlined text-[20px]">mic</span>
                            <p class="text-lg font-normal">Chris Tomlin</p>
                        </div>
                    </div>
                    <button
                        class="flex items-center justify-center gap-2 rounded-lg h-10 px-5 bg-slate-900 dark:bg-white text-white dark:text-black text-sm font-bold hover:bg-slate-800 dark:hover:bg-gray-200 transition-colors shadow-md"
                        type="button">
                        <span class="material-symbols-outlined text-[20px] material-symbols-filled">slideshow</span>
                        <span>Modo Presentación</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div
                        class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
                        <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                            Tonalidad</p>
                        <p class="text-slate-900 dark:text-white text-xl font-bold">F Mayor</p>
                    </div>
                    <div
                        class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
                        <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                            BPM</p>
                        <p class="text-slate-900 dark:text-white text-xl font-bold">72</p>
                    </div>
                    <div
                        class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
                        <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                            Compás</p>
                        <p class="text-slate-900 dark:text-white text-xl font-bold">4/4</p>
                    </div>
                    <div
                        class="flex flex-col p-4 rounded-xl bg-white dark:bg-[#1c2128] border border-gray-200 dark:border-[#283039] shadow-sm">
                        <p class="text-slate-500 dark:text-[#9dabb9] text-xs font-bold uppercase tracking-wider mb-1">
                            Duración</p>
                        <p class="text-slate-900 dark:text-white text-xl font-bold">4:23</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full min-h-[600px]">
                    <div
                        class="lg:col-span-7 xl:col-span-8 flex flex-col bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
                        <div
                            class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-[#283039] bg-white/50 dark:bg-[#1c2128]/50 backdrop-blur-sm sticky top-0 z-10">
                            <h3 class="text-slate-900 dark:text-white font-bold text-lg">Letra y Acordes</h3>
                            <div
                                class="flex items-center gap-2 bg-gray-100 dark:bg-[#111418] rounded-lg p-1 border border-gray-200 dark:border-[#283039]">
                                <button
                                    class="size-8 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                                    type="button">
                                    <span class="material-symbols-outlined text-[20px]">remove</span>
                                </button>
                                <span
                                    class="text-xs font-mono font-bold text-primary px-2 min-w-[3rem] text-center">F</span>
                                <button
                                    class="size-8 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                                    type="button">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                </button>
                            </div>
                            <div class="hidden sm:flex gap-1">
                                <button
                                    class="size-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                                    title="Toggle Chords" type="button">
                                    <span class="material-symbols-outlined text-[20px]">music_note</span>
                                </button>
                                <button
                                    class="size-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-[#283039] text-slate-500 dark:text-[#9dabb9] hover:text-slate-900 dark:hover:text-white transition-colors"
                                    title="Settings" type="button">
                                    <span class="material-symbols-outlined text-[20px]">settings</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-6 md:p-8 overflow-y-auto max-h-[800px]">
                            <div
                                class="font-mono text-base md:text-lg leading-loose text-slate-700 dark:text-white/90 whitespace-pre-wrap">
                                <span class="text-primary font-bold">Intro</span>
                                F Bb F C
                                <span class="text-primary font-bold">Verso 1</span>
                                F Bb F
                                Amazing grace how sweet the sound
                                C
                                That saved a wretch like me
                                F Bb F
                                I once was lost, but now I'm found
                                C F
                                Was blind but now I see
                                <span class="text-primary font-bold">Coro</span>
                                Bb F
                                My chains are gone, I've been set free
                                Bb F
                                My God, my Savior has ransomed me
                                Bb F
                                And like a flood His mercy rains
                                C7 F
                                Unending love, amazing grace
                                <span class="text-primary font-bold">Verso 2</span>
                                'Twas grace that taught my heart to fear
                                And grace my fears relieved
                                How precious did that grace appear
                                The hour I first believed
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
                        <div
                            class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] p-4 flex flex-col gap-3 shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="size-12 rounded-lg bg-cover bg-center shrink-0 shadow-sm"
                                    data-alt="Abstract album art cover with blue and purple gradients"
                                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBKGmAjgmzYKHvzXf7T7TZiUFSVQqc5nmnTIWQi6F4M7XuUw_6uATukUdeJ1tDgJYOF7oQvldG2biLUFtmN_N7nVyO8Wifre7OtAXdsqfI9YJv5I76UJwUF3kLsl23ms82olGb2IgKOHfJvxxsmuVjZBkl98Y_E-cQZRt4RNP4c56Dx_5ovdPBAli806kz8qsQVzCHNFMZMdVxQEQgrUjCaOAC3S65DWWfGYGAnJNJuumQWbQBEaWylajKj6DGb04WKWU6nTcwGczI8");'>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-slate-900 dark:text-white text-sm font-bold truncate">Amazing Grace
                                        (My Chains Are Gone)</p>
                                    <p class="text-slate-500 dark:text-[#9dabb9] text-xs truncate">Tono Original: F</p>
                                </div>
                            </div>
                            <div
                                class="w-full bg-gray-200 dark:bg-[#283039] h-1.5 rounded-full overflow-hidden mt-1 cursor-pointer">
                                <div class="bg-primary h-full w-1/3 rounded-full"></div>
                            </div>
                            <div
                                class="flex justify-between items-center text-slate-400 dark:text-[#9dabb9] text-xs font-mono">
                                <span>1:12</span>
                                <span>4:23</span>
                            </div>
                            <div class="flex justify-center items-center gap-4">
                                <button
                                    class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white transition-colors"
                                    type="button"><span class="material-symbols-outlined">skip_previous</span></button>
                                <button
                                    class="size-10 flex items-center justify-center rounded-full bg-primary text-white shadow-lg hover:bg-primary/90 transition transform hover:scale-105"
                                    type="button">
                                    <span class="material-symbols-outlined material-symbols-filled">play_arrow</span>
                                </button>
                                <button
                                    class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white transition-colors"
                                    type="button"><span class="material-symbols-outlined">skip_next</span></button>
                            </div>
                        </div>
                        <div
                            class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
                            <div
                                class="px-4 py-3 border-b border-gray-200 dark:border-[#283039] flex justify-between items-center bg-gray-50 dark:bg-[#1c2128]">
                                <h3 class="text-slate-900 dark:text-white font-bold text-sm">Archivos y Partituras</h3>
                                <button class="text-primary text-xs font-bold hover:underline" type="button">Ver
                                    Todo</button>
                            </div>
                            <div class="flex flex-col">
                                <div
                                    class="group flex items-center justify-between p-3 border-b border-gray-100 dark:border-[#283039] hover:bg-gray-50 dark:hover:bg-[#283039]/50 transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-8 rounded bg-red-100 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-500">
                                            <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <p class="text-slate-900 dark:text-white text-sm font-medium">Carta de
                                                Acordes (F)</p>
                                            <p class="text-slate-500 dark:text-[#9dabb9] text-xs">PDF • 124 KB</p>
                                        </div>
                                    </div>
                                    <button
                                        class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white opacity-0 group-hover:opacity-100 transition"
                                        type="button">
                                        <span class="material-symbols-outlined">download</span>
                                    </button>
                                </div>
                                <div
                                    class="group flex items-center justify-between p-3 border-b border-gray-100 dark:border-[#283039] hover:bg-gray-50 dark:hover:bg-[#283039]/50 transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-8 rounded bg-blue-100 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-500">
                                            <span class="material-symbols-outlined text-[20px]">music_note</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <p class="text-slate-900 dark:text-white text-sm font-medium">Partitura
                                                Melódica</p>
                                            <p class="text-slate-500 dark:text-[#9dabb9] text-xs">PDF • 85 KB</p>
                                        </div>
                                    </div>
                                    <button
                                        class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white opacity-0 group-hover:opacity-100 transition"
                                        type="button">
                                        <span class="material-symbols-outlined">download</span>
                                    </button>
                                </div>
                                <div
                                    class="group flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-[#283039]/50 transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-8 rounded bg-green-100 dark:bg-green-500/10 flex items-center justify-center text-green-600 dark:text-green-500">
                                            <span class="material-symbols-outlined text-[20px]">audio_file</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <p class="text-slate-900 dark:text-white text-sm font-medium">Pista (Sin
                                                Voz)</p>
                                            <p class="text-slate-500 dark:text-[#9dabb9] text-xs">MP3 • 4.2 MB</p>
                                        </div>
                                    </div>
                                    <button
                                        class="text-slate-400 dark:text-[#9dabb9] hover:text-primary dark:hover:text-white opacity-0 group-hover:opacity-100 transition"
                                        type="button">
                                        <span class="material-symbols-outlined">download</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-white dark:bg-[#1c2128] rounded-xl border border-gray-200 dark:border-[#283039] overflow-hidden shadow-sm">
                            <div
                                class="px-4 py-3 border-b border-gray-200 dark:border-[#283039] bg-gray-50 dark:bg-[#1c2128]">
                                <h3 class="text-slate-900 dark:text-white font-bold text-sm">Tutoriales y Videos</h3>
                            </div>
                            <div class="p-3 grid grid-cols-2 gap-3">
                                <div
                                    class="relative aspect-video rounded-lg overflow-hidden group cursor-pointer shadow-sm">
                                    <div class="absolute inset-0 bg-cover bg-center transition transform group-hover:scale-105"
                                        data-alt="Person playing acoustic guitar close up"
                                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCC_vl0BRQjdzVq3KLkXSHWl37LLmiXBndkXlm9a_Yg_uAzeS89_0f1Yh4Buzat3paIYWOq9RdJLdGDiOJhngVT6zm3333z87QAXIRMgYQ0lRNXWwQagtw1vQt00nvLGo21_eD8QgW4k6LYY9795O4H-kDOIMvYJL4_ibxdkhie_68jF6adxUA6ghisi-OFoGtgKpyoYkZyLMf6_WSCr93qOJed5EX3S8H8a7bcGHpmcrAIY0h2pQ1JhVzp1PBiPMLgXgtq3oU16E1h");'>
                                    </div>
                                    <div
                                        class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition flex items-center justify-center">
                                        <div
                                            class="size-10 rounded-full bg-white/30 backdrop-blur-md flex items-center justify-center text-white group-hover:scale-110 transition shadow-lg">
                                            <span
                                                class="material-symbols-outlined material-symbols-filled text-[24px]">play_arrow</span>
                                        </div>
                                    </div>
                                    <div class="absolute bottom-2 left-2 right-2">
                                        <p class="text-white text-xs font-bold drop-shadow-md truncate">Tutorial
                                            Acústico</p>
                                    </div>
                                </div>
                                <div
                                    class="relative aspect-video rounded-lg overflow-hidden group cursor-pointer shadow-sm">
                                    <div class="absolute inset-0 bg-cover bg-center transition transform group-hover:scale-105"
                                        data-alt="Drum set on stage with blue lighting"
                                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuC2uFrQrqxE_zba_qDkHBtUPUdE2gt0GSycze4O8cJ9kI92jsrNxYm9YxYzvhHU8pq8hNasv9QeEukQQmB5t95TvhEa5TrPbCESC2NgPon7pPfAGdRDTQOspg_hTwbk5OboxMonsapITezWygucmMA7_D7p0p6emCtyEc-1-y9XcJlZ7b78EjLx4jw4zDxPXq-QLzFr1oI2jf5YJiWvWDipDQWvFZseOSkpjC_JKqLD0UkH1xZbqTYs10s7NFkTLSzDBTKrIq19KRf5");'>
                                    </div>
                                    <div
                                        class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition flex items-center justify-center">
                                        <div
                                            class="size-10 rounded-full bg-white/30 backdrop-blur-md flex items-center justify-center text-white group-hover:scale-110 transition shadow-lg">
                                            <span
                                                class="material-symbols-outlined material-symbols-filled text-[24px]">play_arrow</span>
                                        </div>
                                    </div>
                                    <div class="absolute bottom-2 left-2 right-2">
                                        <p class="text-white text-xs font-bold drop-shadow-md truncate">Cámara de
                                            Batería</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label
                            class="rounded-xl border-2 border-dashed border-gray-300 dark:border-[#283039] bg-gray-50 dark:bg-[#1c2128]/50 hover:bg-white dark:hover:bg-[#1c2128] hover:border-primary/50 dark:hover:border-primary/50 transition p-6 flex flex-col items-center justify-center gap-3 cursor-pointer group">
                            <input class="hidden" multiple="" type="file" />
                            <div
                                class="size-10 rounded-full bg-gray-200 dark:bg-[#283039] group-hover:bg-primary/10 dark:group-hover:bg-primary/20 flex items-center justify-center text-slate-500 dark:text-[#9dabb9] group-hover:text-primary transition">
                                <span class="material-symbols-outlined">cloud_upload</span>
                            </div>
                            <div class="text-center">
                                <p class="text-slate-900 dark:text-white text-sm font-bold">Subir Recurso</p>
                                <p class="text-slate-500 dark:text-[#9dabb9] text-xs">Arrastra archivos aquí o haz clic
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>