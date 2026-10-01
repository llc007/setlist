<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Setlist') }} — El setlist perfecto para tu banda en vivo</title>
    <meta name="description" content="Organiza tu repertorio, sincroniza tonos y acordes al instante, y comparte setlists interactivos para tus ensayos y presentaciones en vivo.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        ::selection {
            background-color: #1ed760;
            color: #000000;
        }
    </style>
</head>
<body class="min-h-full bg-[#121212] text-white font-sans antialiased selection:bg-[#1ed760] selection:text-black">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#121212]/95 backdrop-blur-md border-b border-[#242424]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1ed760] rounded-lg p-1">
                <div class="flex size-10 items-center justify-center rounded-full bg-[#1ed760] text-black shadow-lg shadow-[#1ed760]/20 transition-transform group-hover:scale-105">
                    <svg class="size-6 text-black" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z" />
                    </svg>
                </div>
                <div class="flex items-baseline">
                    <span class="text-2xl font-black tracking-tight text-white">setlist</span>
                    <span class="text-2xl font-black text-[#1ed760]">.</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-[#a7a7a7]">
                <a href="#funciones" class="hover:text-white transition-colors">Funciones</a>
                <a href="#en-vivo" class="hover:text-white transition-colors">En Vivo</a>
                <a href="#demo" class="hover:text-white transition-colors">Vista Previa</a>
                <a href="#faq" class="hover:text-white transition-colors">Preguntas</a>
            </nav>

            <!-- Auth Actions -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1ed760] hover:bg-[#1db954] text-black font-bold text-sm transition-all shadow-md hover:scale-105 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Ir al panel</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-semibold text-[#a7a7a7] hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1ed760] rounded-full"
                        >
                            Iniciar sesión
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-[#1ed760] hover:bg-[#1db954] text-black font-bold text-sm tracking-wide transition-all shadow-md shadow-[#1ed760]/20 hover:scale-105 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                Registrarse gratis
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <main id="main">
        <!-- Hero Section with Interactive Lattice -->
        <section id="hero-section" class="relative pt-16 pb-24 lg:pt-24 lg:pb-32 overflow-hidden">
            <!-- Interactive Canvas Lattice (Grid of nested squares responding to cursor) -->
            <canvas id="hero-lattice" class="absolute inset-0 w-full h-full pointer-events-none z-0 opacity-90"></canvas>

            <!-- Radial Vignette for crystal-clear text contrast and seamless blending -->
            <div class="absolute inset-0 pointer-events-none z-0 bg-[radial-gradient(ellipse_at_center,rgba(18,18,18,0.65)_0%,rgba(18,18,18,0.92)_70%,#121212_100%)]"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-tight drop-shadow-md">
                        El setlist perfecto para tu banda en vivo.
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-[#a7a7a7] leading-relaxed">
                        Organiza tu repertorio, sincroniza acordes y tonos al instante, y comparte el orden de canciones en directo para ensayos y conciertos sin fricción.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-full bg-[#1ed760] hover:bg-[#1db954] text-black font-black text-base transition-all shadow-xl shadow-[#1ed760]/25 hover:scale-105 active:scale-95 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#1ed760]/40"
                            >
                                Empieza gratis ahora
                            </a>
                        @endif
                        <a
                            href="#demo"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-full bg-[#181818] hover:bg-[#242424] border border-[#333333] hover:border-white text-white font-bold text-base transition-all hover:scale-105 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            Ver demostración
                        </a>
                    </div>
                    <p class="mt-4 text-xs text-[#a7a7a7]">
                        Sin tarjetas de crédito · Comparte enlaces públicos por WhatsApp sin requerir registro a tus músicos
                    </p>
                </div>

                <!-- Interactive App Preview (Spotify Player & Setlist Console) -->
                <div id="demo" class="max-w-4xl mx-auto rounded-2xl bg-[#181818] border border-[#282828] shadow-2xl p-4 sm:p-8">
                    <!-- Console Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#282828] gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-full bg-[#1ed760]/10 text-[#1ed760] text-xs font-bold uppercase tracking-wider mb-2">
                                En vivo hoy · 20:30 hrs
                            </span>
                            <h2 class="text-2xl font-bold text-white tracking-tight">
                                Concierto Acústico — Sala Principal
                            </h2>
                            <p class="text-sm text-[#a7a7a7] mt-1">
                                Banda Central · 8 temas · 42 min de duración total
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#242424] border border-[#333333] text-xs text-[#a7a7a7]">
                                <span class="size-2 rounded-full bg-[#1ed760] animate-pulse"></span>
                                Modo escenario activo
                            </span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#242424] hover:bg-[#333333] text-white text-xs font-bold transition-colors border border-[#333333]"
                                title="Copiar enlace de WhatsApp"
                            >
                                <svg class="size-4 text-[#1ed760]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Compartir link</span>
                            </button>
                        </div>
                    </div>

                    <!-- Song List Mockup -->
                    <div class="space-y-2">
                        <!-- Song 1 (Active) -->
                        <div class="group flex items-center justify-between p-3.5 rounded-xl bg-[#242424] border border-[#1ed760]/30 transition-colors">
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="flex size-7 items-center justify-center rounded-full bg-[#1ed760] text-black font-black text-xs shrink-0">
                                    1
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-white text-base truncate">Gracia Sublime Es</p>
                                        <span class="px-2 py-0.5 rounded bg-black/40 text-[#1ed760] font-mono text-xs font-bold border border-[#1ed760]/20">
                                            G
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#a7a7a7] truncate">Intro guitarra acústica · 74 BPM · 4/4</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="hidden sm:inline-block text-xs font-mono text-[#a7a7a7]">04:30</span>
                                <span class="px-2.5 py-1 rounded-full bg-[#181818] text-xs font-semibold text-[#1ed760] border border-[#333333]">
                                    Tocando ahora
                                </span>
                            </div>
                        </div>

                        <!-- Song 2 -->
                        <div class="group flex items-center justify-between p-3.5 rounded-xl hover:bg-[#202020] transition-colors">
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="flex size-7 items-center justify-center rounded-full bg-[#242424] text-[#a7a7a7] font-bold text-xs shrink-0">
                                    2
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-white text-base truncate">Way Maker (Milagroso)</p>
                                        <span class="px-2 py-0.5 rounded bg-[#242424] text-white font-mono text-xs font-bold border border-[#333333]">
                                            E
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#a7a7a7] truncate">Transición suave desde piano · 68 BPM</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="hidden sm:inline-block text-xs font-mono text-[#a7a7a7]">05:15</span>
                                <span class="px-2 py-1 rounded bg-[#242424] text-xs text-[#a7a7a7]">PDF adjunto</span>
                            </div>
                        </div>

                        <!-- Song 3 -->
                        <div class="group flex items-center justify-between p-3.5 rounded-xl hover:bg-[#202020] transition-colors">
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="flex size-7 items-center justify-center rounded-full bg-[#242424] text-[#a7a7a7] font-bold text-xs shrink-0">
                                    3
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-white text-base truncate">Rey de Reyes</p>
                                        <span class="px-2 py-0.5 rounded bg-[#242424] text-white font-mono text-xs font-bold border border-[#333333]">
                                            D
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#a7a7a7] truncate">Subida de intensidad con batería · 130 BPM</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="hidden sm:inline-block text-xs font-mono text-[#a7a7a7]">04:10</span>
                                <span class="px-2 py-1 rounded bg-[#242424] text-xs text-[#a7a7a7]">Letra + Acordes</span>
                            </div>
                        </div>

                        <!-- Song 4 -->
                        <div class="group flex items-center justify-between p-3.5 rounded-xl hover:bg-[#202020] transition-colors">
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="flex size-7 items-center justify-center rounded-full bg-[#242424] text-[#a7a7a7] font-bold text-xs shrink-0">
                                    4
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-white text-base truncate">La Bendición</p>
                                        <span class="px-2 py-0.5 rounded bg-[#242424] text-white font-mono text-xs font-bold border border-[#333333]">
                                            B
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#a7a7a7] truncate">Cierre acústico · 70 BPM · Con coro final</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="hidden sm:inline-block text-xs font-mono text-[#a7a7a7]">06:00</span>
                                <span class="px-2 py-1 rounded bg-[#242424] text-xs text-[#a7a7a7]">Audio ref</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Actions Bar -->
                    <div class="mt-6 pt-6 border-t border-[#282828] flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-[#a7a7a7]">Transponer tono global:</span>
                            <div class="inline-flex rounded-lg border border-[#333333] bg-[#242424] p-0.5">
                                <button type="button" class="px-2.5 py-1 text-xs font-bold text-white hover:text-[#1ed760] transition-colors">-1</button>
                                <span class="px-2 py-1 text-xs font-mono text-[#1ed760] border-x border-[#333333]">Original</span>
                                <button type="button" class="px-2.5 py-1 text-xs font-bold text-white hover:text-[#1ed760] transition-colors">+1</button>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('repertorio') }}"
                                class="text-xs font-bold text-[#1ed760] hover:underline"
                            >
                                Explorar catálogo completo →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid (Distinct layouts, not repetitive cards) -->
        <section id="funciones" class="py-24 bg-[#181818] border-y border-[#242424]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-16">
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
                        Construido para la realidad del escenario.
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-[#a7a7a7]">
                        Se acabaron las capturas borrosas en el grupo de chat, los papeles arrugados sobre los parlantes y las confusiones de último minuto con el tono de la canción.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-[#121212] border border-[#282828] flex flex-col justify-between hover:border-[#1ed760]/40 transition-colors">
                        <div>
                            <div class="size-12 rounded-xl bg-[#1ed760]/10 text-[#1ed760] flex items-center justify-center mb-6">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white tracking-tight">
                                Lectura táctica en atril
                            </h3>
                            <p class="mt-3 text-sm text-[#a7a7a7] leading-relaxed">
                                Tipografía Inter optimizada para verse clara a más de un metro de distancia. Modo oscuro nativo que no te encandila en escenarios con poca iluminación.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#242424] text-xs font-bold text-[#1ed760]">
                            Contraste certificado WCAG AAA
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-[#121212] border border-[#282828] flex flex-col justify-between hover:border-[#1ed760]/40 transition-colors">
                        <div>
                            <div class="size-12 rounded-xl bg-[#1ed760]/10 text-[#1ed760] flex items-center justify-center mb-6">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white tracking-tight">
                                WhatsApp directo sin login
                            </h3>
                            <p class="mt-3 text-sm text-[#a7a7a7] leading-relaxed">
                                Envía el enlace público al grupo de la banda. Los músicos invitados abren el setlist al instante en sus teléfonos sin crear cuenta ni recordar claves.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#242424] text-xs font-bold text-[#1ed760]">
                            Cero fricción de adopción
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-[#121212] border border-[#282828] flex flex-col justify-between hover:border-[#1ed760]/40 transition-colors">
                        <div>
                            <div class="size-12 rounded-xl bg-[#1ed760]/10 text-[#1ed760] flex items-center justify-center mb-6">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white tracking-tight">
                                Todo el material en un lugar
                            </h3>
                            <p class="mt-3 text-sm text-[#a7a7a7] leading-relaxed">
                                Adjunta partituras PDF, acordes, audios de YouTube/Spotify y notas de ensayo por canción. Cada integrante sabe exactamente qué tocar.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#242424] text-xs font-bold text-[#1ed760]">
                            Acordes, audios y notas
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Workflow Section -->
        <section id="en-vivo" class="py-24 bg-[#121212]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                            Diseñado tanto para quien dirige como para quien toca.
                        </h2>
                        <div class="mt-8 space-y-6">
                            <div class="flex gap-4">
                                <div class="size-8 rounded-full bg-[#1ed760] text-black font-black flex items-center justify-center shrink-0 text-sm">
                                    ✓
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">Reordena el orden arrastrando canciones</h3>
                                    <p class="mt-1 text-sm text-[#a7a7a7]">
                                        Modifica la lista minutos antes del concierto si el tiempo apremia; el setlist de los músicos se actualiza sin enviar nuevos mensajes.
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="size-8 rounded-full bg-[#1ed760] text-black font-black flex items-center justify-center shrink-0 text-sm">
                                    ✓
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">Tono y tempo a la vista</h3>
                                    <p class="mt-1 text-sm text-[#a7a7a7]">
                                        El bajista y el tecladista saben en qué nota arrancar sin tener que hacer señas con las manos en el escenario.
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="size-8 rounded-full bg-[#1ed760] text-black font-black flex items-center justify-center shrink-0 text-sm">
                                    ✓
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">Múltiples bandas y equipos</h3>
                                    <p class="mt-1 text-sm text-[#a7a7a7]">
                                        Gestiona diferentes grupos, coros o ensambles desde una misma cuenta sin mezclar los repertorios.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10">
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center px-8 py-4 rounded-full bg-[#1ed760] hover:bg-[#1db954] text-black font-black text-sm tracking-wide transition-all shadow-lg shadow-[#1ed760]/20 hover:scale-105 active:scale-95"
                                >
                                    Crear mi banda en Setlist
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Real Stage Tablet Visual -->
                    <div class="rounded-2xl bg-[#181818] border border-[#282828] p-6 lg:p-8 shadow-2xl">
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-[#282828]">
                            <div class="flex items-center gap-2">
                                <div class="size-3 rounded-full bg-[#333333]"></div>
                                <div class="size-3 rounded-full bg-[#333333]"></div>
                                <div class="size-3 rounded-full bg-[#333333]"></div>
                            </div>
                            <span class="text-xs font-mono text-[#a7a7a7]">setlist.app/setlist/acustico-2026</span>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 rounded-xl bg-[#121212] border border-[#282828]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-[#a7a7a7]">Tema 1 de 8</span>
                                    <span class="text-xs font-mono px-2 py-0.5 rounded bg-[#1ed760]/10 text-[#1ed760] font-bold">Tono: D (Re Mayor)</span>
                                </div>
                                <h4 class="text-xl font-bold text-white">Rey de Reyes</h4>
                                <div class="mt-4 p-3 rounded-lg bg-[#181818] font-mono text-xs text-white leading-relaxed overflow-x-auto">
                                    <span class="text-[#1ed760] font-bold">[Intro]</span> D  G  Bm  A<br>
                                    <span class="text-[#1ed760] font-bold">[Verso 1]</span><br>
                                    D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; G<br>
                                    En la oscuridad estábamos<br>
                                    Bm &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; A<br>
                                    Sin esperanza para ver
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-lg bg-[#242424] text-xs">
                                <span class="text-[#a7a7a7]">Siguiente canción:</span>
                                <span class="font-bold text-white">Gracia Sublime (Tono G) →</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-24 bg-[#181818] border-t border-[#242424]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
                        Preguntas frecuentes
                    </h2>
                    <p class="mt-4 text-[#a7a7a7]">
                        Todo lo que necesitas saber antes de subir a tocar con Setlist.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="p-6 rounded-xl bg-[#121212] border border-[#282828]">
                        <h3 class="text-lg font-bold text-white">¿Mis músicos necesitan registrarse para ver las canciones?</h3>
                        <p class="mt-2 text-sm text-[#a7a7a7] leading-relaxed">
                            No. Solo el director o quien arma el setlist necesita cuenta. Tus músicos reciben un enlace web público que pueden abrir directamente en WhatsApp desde su navegador móvil.
                        </p>
                    </div>

                    <div class="p-6 rounded-xl bg-[#121212] border border-[#282828]">
                        <h3 class="text-lg font-bold text-white">¿Puedo subir partituras o acordes en formato PDF?</h3>
                        <p class="mt-2 text-sm text-[#a7a7a7] leading-relaxed">
                            Sí. Puedes adjuntar archivos PDF a cualquier canción de tu repertorio, además de enlaces a audios de referencia de YouTube o Spotify y notas de ejecución.
                        </p>
                    </div>

                    <div class="p-6 rounded-xl bg-[#121212] border border-[#282828]">
                        <h3 class="text-lg font-bold text-white">¿Funciona bien en teléfonos móviles sobre el atril?</h3>
                        <p class="mt-2 text-sm text-[#a7a7a7] leading-relaxed">
                            Totalmente. La interfaz está diseñada mobile-first con contrastes certificados y controles táctiles de al menos 44px de altura para evitar toques accidentales en vivo.
                        </p>
                    </div>

                    <div class="p-6 rounded-xl bg-[#121212] border border-[#282828]">
                        <h3 class="text-lg font-bold text-white">¿Tiene algún costo para empezar?</h3>
                        <p class="mt-2 text-sm text-[#a7a7a7] leading-relaxed">
                            Setlist es gratuito para crear tu banda, registrar tus canciones y armar tus primeros eventos.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Banner -->
        <section class="py-24 bg-[#121212] text-center border-t border-[#242424]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tight">
                    Haz que tu próximo concierto suene sin interrupciones.
                </h2>
                <p class="mt-6 text-lg text-[#a7a7a7] max-w-2xl mx-auto">
                    Únete a las bandas y directores de alabanza que ya organizan su música con Setlist.
                </p>
                <div class="mt-10">
                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center px-10 py-5 rounded-full bg-[#1ed760] hover:bg-[#1db954] text-black font-black text-lg transition-all shadow-xl shadow-[#1ed760]/30 hover:scale-105 active:scale-95 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#1ed760]/40"
                        >
                            Crear cuenta gratis ahora
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-[#121212] border-t border-[#242424] py-12 text-sm text-[#a7a7a7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-[#1ed760] text-black">
                    <svg class="size-4 text-black" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z" />
                    </svg>
                </div>
                <span class="font-bold text-white tracking-tight">setlist</span>
                <span>© {{ date('Y') }} Todos los derechos reservados.</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="#funciones" class="hover:text-white transition-colors">Funciones</a>
                <a href="#en-vivo" class="hover:text-white transition-colors">En Vivo</a>
                <a href="#faq" class="hover:text-white transition-colors">Preguntas</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Ingresar</a>
                @endif
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('hero-lattice');
            const hero = document.getElementById('hero-section');
            if (!canvas || !hero) return;

            const ctx = canvas.getContext('2d');
            let width = 0;
            let height = 0;
            let dpr = 1;

            const cellSize = 15;
            let cols = 0;
            let rows = 0;

            const mouse = {
                x: -1000,
                y: -1000,
                targetX: -1000,
                targetY: -1000,
                isHovered: false,
                radius: 240,
                ripple: 0
            };

            function resize() {
                const rect = hero.getBoundingClientRect();
                width = rect.width;
                height = rect.height;
                dpr = Math.min(window.devicePixelRatio || 1, 2);

                canvas.width = Math.floor(width * dpr);
                canvas.height = Math.floor(height * dpr);
                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                cols = Math.ceil(width / cellSize);
                rows = Math.ceil(height / cellSize);

                if (!mouse.isHovered && mouse.targetX === -1000) {
                    mouse.x = width * 0.5;
                    mouse.y = height * 0.35;
                    mouse.targetX = mouse.x;
                    mouse.targetY = mouse.y;
                }
            }

            hero.addEventListener('pointermove', (e) => {
                const rect = hero.getBoundingClientRect();
                mouse.targetX = e.clientX - rect.left;
                mouse.targetY = e.clientY - rect.top;
                mouse.isHovered = true;
            }, { passive: true });

            hero.addEventListener('pointerleave', () => {
                mouse.isHovered = false;
            });

            hero.addEventListener('pointerdown', () => {
                mouse.ripple = 1.0;
            });

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let time = 0;

            function render() {
                ctx.clearRect(0, 0, width, height);

                if (mouse.ripple > 0) {
                    mouse.ripple *= 0.94;
                    if (mouse.ripple < 0.01) mouse.ripple = 0;
                }

                if (mouse.isHovered) {
                    mouse.x += (mouse.targetX - mouse.x) * 0.12;
                    mouse.y += (mouse.targetY - mouse.y) * 0.12;
                } else {
                    time += 0.012;
                    const cx = width * 0.5;
                    const cy = height * 0.38;
                    mouse.targetX = cx + Math.cos(time * 0.7) * (width * 0.22);
                    mouse.targetY = cy + Math.sin(time * 0.9) * (height * 0.14);
                    mouse.x += (mouse.targetX - mouse.x) * 0.04;
                    mouse.y += (mouse.targetY - mouse.y) * 0.04;
                }

                const currentRadius = mouse.radius + mouse.ripple * 100;
                const currentRadiusSq = currentRadius * currentRadius;

                for (let r = 0; r < rows; r++) {
                    const y = r * cellSize + cellSize * 0.5;
                    for (let c = 0; c < cols; c++) {
                        const x = c * cellSize + cellSize * 0.5;

                        const dx = x - mouse.x;
                        const dy = y - mouse.y;
                        const distSq = dx * dx + dy * dy;

                        // Ambient organic contour noise
                        const wave1 = Math.sin(x * 0.016 + time * 0.8) * Math.cos(y * 0.018 - time * 0.6);
                        const wave2 = Math.sin((x + y) * 0.022 + time * 0.4);
                        const ambient = (wave1 + wave2) * 0.5;

                        let cursorInfluence = 0;
                        if (distSq < currentRadiusSq) {
                            const dist = Math.sqrt(distSq);
                            cursorInfluence = 1 - (dist / currentRadius);
                            cursorInfluence = cursorInfluence * cursorInfluence * (3 - 2 * cursorInfluence);
                        }

                        const combined = Math.max(0, Math.min(1, cursorInfluence * 0.85 + ambient * 0.22));

                        if (combined < 0.04) continue;

                        const boxSize = Math.max(3, (cellSize - 3) * (0.35 + combined * 0.65));
                        const halfBox = boxSize * 0.5;

                        let strokeColor, fillColor;

                        if (combined > 0.72) {
                            // Turquoise / Electric Mint core
                            strokeColor = 'rgba(0, 245, 196, ' + Math.min(1, combined * 0.95).toFixed(2) + ')';
                            fillColor = 'rgba(30, 215, 96, ' + Math.min(1, combined * 0.95).toFixed(2) + ')';
                        } else if (combined > 0.35) {
                            // Vibrant Spotify green
                            const t = (combined - 0.35) / 0.37;
                            strokeColor = 'rgba(30, 215, 96, ' + (0.4 + t * 0.55).toFixed(2) + ')';
                            fillColor = 'rgba(22, 163, 74, ' + (0.3 + t * 0.5).toFixed(2) + ')';
                        } else {
                            // Subtle background contour
                            strokeColor = 'rgba(34, 197, 94, ' + (combined * 0.55).toFixed(2) + ')';
                            fillColor = 'rgba(24, 40, 28, ' + (combined * 0.4).toFixed(2) + ')';
                        }

                        // Outer Square
                        ctx.strokeStyle = strokeColor;
                        ctx.lineWidth = 1;
                        ctx.strokeRect(x - halfBox, y - halfBox, boxSize, boxSize);

                        // Inner concentric filled square (as in reference image)
                        const innerSize = Math.max(1.5, boxSize * (0.3 + combined * 0.35));
                        const halfInner = innerSize * 0.5;
                        ctx.fillStyle = fillColor;
                        ctx.fillRect(x - halfInner, y - halfInner, innerSize, innerSize);
                    }
                }

                if (!prefersReducedMotion) {
                    requestAnimationFrame(render);
                }
            }

            window.addEventListener('resize', resize, { passive: true });
            resize();

            if (!prefersReducedMotion) {
                requestAnimationFrame(render);
            } else {
                render();
            }
        });
    </script>
</body>
</html>
