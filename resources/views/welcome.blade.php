<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'QueCantamos') }} — El setlist perfecto para tu banda en vivo</title>
    <meta name="description" content="Organiza tu repertorio, sincroniza acordes y tonos, y comparte setlists en directo para ensayos y conciertos. Diseño vintage, rápido y sin complicaciones.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-serif-vintage {
            font-family: 'Instrument Serif', Georgia, serif;
        }
        .font-mono-vintage {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Vinyl Grooves Texture */
        .vinyl-grooves {
            background:
                radial-gradient(circle at 50% 50%, #151618 0%, #101113 25%, #18191c 26%, #0f1011 40%, #17181a 41%, #0b0c0d 60%, #151618 61%, #0d0e0f 72%, #18191b 73%, #080809 100%);
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.75),
                0 0 0 1px rgba(255, 255, 255, 0.08),
                inset 0 0 35px rgba(0, 0, 0, 0.95);
        }

        /* Vinyl Light Sheen (Specular highlights) */
        .vinyl-sheen {
            background: conic-gradient(
                from var(--sheen-angle, 45deg) at 50% 50%,
                rgba(255, 255, 255, 0.01) 0deg,
                rgba(255, 255, 255, 0.16) 45deg,
                rgba(255, 255, 255, 0.02) 90deg,
                rgba(255, 255, 255, 0.01) 180deg,
                rgba(255, 255, 255, 0.18) 225deg,
                rgba(255, 255, 255, 0.02) 270deg,
                rgba(255, 255, 255, 0.01) 360deg
            );
            mix-blend-mode: screen;
            pointer-events: none;
        }

        /* Vinyl Spin Keyframes */
        @keyframes vinylSpin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .vinyl-rotating {
            animation: vinylSpin var(--spin-duration, 2.2s) linear infinite;
        }

        .vinyl-paused {
            animation-play-state: paused;
        }

        /* Subtle floating notes animation */
        @keyframes gentleFloat {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-8px) rotate(3deg);
            }
        }

        .floating-element {
            animation: gentleFloat 4s ease-in-out infinite;
        }

        /* Custom scroll behavior & text selection */
        ::selection {
            background-color: #f59e0b;
            color: #000000;
        }
    </style>
</head>
<body class="min-h-full bg-[#0d0f12] text-[#f4f1ea] antialiased overflow-x-hidden selection:bg-amber-500 selection:text-black">

    <!-- Ambient Vintage Background Glows -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-amber-600/10 rounded-full blur-[140px]"></div>
        <div class="absolute top-1/3 -right-40 w-[650px] h-[650px] bg-amber-500/10 rounded-full blur-[160px]"></div>
        <div class="absolute -bottom-40 left-1/4 w-[500px] h-[500px] bg-orange-700/10 rounded-full blur-[150px]"></div>
        <!-- Grain overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(#ffffff08_1px,transparent_1px)] [background-size:24px_24px] opacity-40"></div>
    </div>

    <!-- Top Navigation Bar (Spotify-inspired minimal header) -->
    <header class="relative z-30 sticky top-0 backdrop-blur-xl bg-[#0d0f12]/85 border-b border-amber-950/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="group flex items-center gap-3 transition-transform hover:scale-[1.02]">
                <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-black p-1 shrink-0 shadow-lg shadow-amber-500/20 border border-amber-300/30">
                    <x-app-logo-icon class="size-7 text-[#0d0f12]" />
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center font-sans tracking-tight">
                        <span class="text-xl font-black text-white tracking-tight">quecantamos</span>
                        <span class="text-xl font-black text-amber-500">.cl</span>
                    </div>
                    <span class="text-[10px] font-mono-vintage text-amber-400/80 tracking-widest uppercase -mt-0.5">High Fidelity Setlists</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="flex items-center gap-2 sm:gap-4">
                @if (Route::has('login'))
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm transition-all shadow-md shadow-amber-500/25 hover:shadow-amber-500/40"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Ir al panel</span>
                        </a>
                    @else
                        <!-- Iniciar Sesión (Login) -->
                        <a
                            href="{{ route('login') }}"
                            class="px-4 sm:px-5 py-2 rounded-full text-zinc-300 hover:text-white font-medium text-sm transition-colors"
                        >
                            Iniciar sesión
                        </a>

                        @if (Route::has('register'))
                            <!-- Crear cuenta (Register) -->
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-bold text-sm tracking-wide transition-all shadow-lg shadow-amber-500/20 hover:shadow-amber-500/35 hover:scale-[1.02] active:scale-[0.98]"
                            >
                                Registrarse
                            </a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10">

        <!-- Hero Section with Mouse-Reactive Vintage Turntable & Vinyl -->
        <section id="hero-container" class="relative min-h-[calc(100vh-5rem)] flex items-center justify-center pt-8 pb-16 lg:py-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Left Column: Copy & CTAs (Spotify Premium Inspired) -->
                <div class="lg:col-span-6 flex flex-col items-start text-left z-20 space-y-6">

                    <!-- Vintage Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-950/40 border border-amber-500/30 text-amber-300 text-xs font-mono-vintage uppercase tracking-wider backdrop-blur-md">
                        <span class="size-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>Edición Analógica • 33 ⅓ RPM</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-serif-vintage tracking-tight text-white leading-[1.05]">
                        Tu repertorio en vivo, <br>
                        <span class="italic text-amber-400">sin notas sueltas.</span>
                    </h1>

                    <!-- Value Proposition -->
                    <p class="text-lg sm:text-xl text-zinc-300 max-w-xl font-normal leading-relaxed">
                        Organiza tus canciones, calcula la duración exacta de tu show y comparte el setlist con tu banda en un solo toque. La herramienta esencial antes de subir al escenario.
                    </p>

                    <!-- Call To Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2 w-full sm:w-auto">
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center px-8 py-4 rounded-full bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-black font-extrabold text-base tracking-wide transition-all shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 hover:scale-[1.03] active:scale-[0.98] group"
                            >
                                <span>Crear cuenta gratis</span>
                                <svg class="size-5 ml-2.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @endif

                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center px-7 py-4 rounded-full bg-white/5 hover:bg-white/10 text-white font-semibold text-base border border-white/15 hover:border-amber-400/40 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <span>Iniciar sesión</span>
                            </a>
                        @endif
                    </div>

                    <!-- Micro-reassurance -->
                    <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-zinc-400 font-mono-vintage">
                        <div class="flex items-center gap-1.5">
                            <svg class="size-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>100% Gratis para bandas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="size-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Sin descargas pesadas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="size-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Móvil, tablet y PC</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Interactive 3D Mouse-Reactive Turntable & Vinyl -->
                <div class="lg:col-span-6 flex items-center justify-center relative perspective-[1200px] select-none py-6">

                    <!-- Floating Ambient Musical Notes & Chords (Parallax Layer) -->
                    <div id="floating-layer" class="absolute inset-0 pointer-events-none z-30 transition-transform duration-300 ease-out">
                        <!-- Floating Chord: [Am7] -->
                        <div class="absolute -top-4 left-6 sm:left-12 px-3 py-1.5 rounded-lg bg-[#1a1c20]/90 border border-amber-500/40 text-amber-300 font-mono-vintage text-xs shadow-xl backdrop-blur-md transform -rotate-6 floating-element">
                            <span class="text-[10px] text-zinc-400 mr-1">ACORDE</span>
                            <span class="font-bold">[Am7]</span>
                        </div>

                        <!-- Floating Musical Note: Treble Clef -->
                        <div class="absolute top-1/4 -left-2 sm:-left-6 size-12 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400/90 font-serif-vintage text-2xl shadow-lg backdrop-blur-sm transform rotate-12 floating-element" style="animation-delay: 1s;">
                            𝄞
                        </div>

                        <!-- Floating Chord: [G/B] -->
                        <div class="absolute -bottom-2 left-16 px-3 py-1.5 rounded-lg bg-[#1a1c20]/90 border border-amber-500/40 text-amber-300 font-mono-vintage text-xs shadow-xl backdrop-blur-md transform rotate-6 floating-element" style="animation-delay: 2s;">
                            <span class="text-[10px] text-zinc-400 mr-1">TONO</span>
                            <span class="font-bold">[G/B]</span>
                        </div>

                        <!-- Floating Vinyl Badge: 120 BPM -->
                        <div class="absolute top-2 right-4 sm:right-8 px-3 py-1.5 rounded-lg bg-[#1a1c20]/90 border border-amber-500/40 text-amber-300 font-mono-vintage text-xs shadow-xl backdrop-blur-md transform rotate-3 floating-element" style="animation-delay: 1.5s;">
                            <span class="text-amber-400 mr-1">●</span>
                            <span class="font-bold">120 BPM</span>
                        </div>

                        <!-- Floating Musical Notes: Beamed Eighths -->
                        <div class="absolute bottom-16 -right-2 sm:-right-4 size-11 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400/90 font-serif-vintage text-xl shadow-lg backdrop-blur-sm transform -rotate-12 floating-element" style="animation-delay: 2.5s;">
                            ♫
                        </div>
                    </div>

                    <!-- 3D Parallax Board / Turntable Platform -->
                    <div
                        id="vinyl-deck"
                        class="relative w-full max-w-[460px] aspect-square rounded-[36px] bg-gradient-to-br from-[#1e2025] via-[#15171a] to-[#0c0d0f] p-5 sm:p-7 shadow-[0_30px_70px_-15px_rgba(0,0,0,0.85)] border border-amber-950/50 transition-transform duration-150 ease-out will-change-transform cursor-pointer group"
                        title="Haz clic para pausar o girar el vinilo"
                    >
                        <!-- Vintage Brushed Brass Top Plate Inset -->
                        <div class="absolute inset-3 rounded-[28px] border border-amber-500/10 pointer-events-none"></div>

                        <!-- Deck Header: Vintage Technical Badges & VU-Meter -->
                        <div class="flex items-center justify-between pb-3 px-1 border-b border-white/5 text-[11px] font-mono-vintage text-zinc-400">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full bg-amber-400 shadow-[0_0_8px_#f59e0b]"></span>
                                <span class="tracking-widest uppercase font-bold text-zinc-200">HI-FI STEREO</span>
                            </div>

                            <!-- Interactive Speed Switcher Button -->
                            <div class="flex items-center gap-1.5 bg-black/40 rounded-full p-1 border border-white/10" onclick="event.stopPropagation();">
                                <button id="btn-rpm-33" type="button" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-black transition-colors" title="Velocidad estándar 33 ⅓ RPM">33 ⅓</button>
                                <button id="btn-rpm-45" type="button" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-zinc-400 hover:text-white transition-colors" title="Velocidad rápida 45 RPM">45</button>
                                <button id="btn-play-toggle" type="button" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-zinc-400 hover:text-white transition-colors" title="Pausar o reanudar">Pausa</button>
                            </div>
                        </div>

                        <!-- Turntable Platter Area -->
                        <div class="relative w-full aspect-square mt-3 flex items-center justify-center">

                            <!-- Vintage Tonearm / Brazo de tocadiscos -->
                            <div id="tonearm" class="absolute -top-3 -right-2 z-20 w-32 h-44 pointer-events-none transition-transform duration-700 ease-out origin-top-right transform -rotate-12">
                                <!-- Pivot Base (Gold & Chrome) -->
                                <div class="absolute top-2 right-2 size-10 rounded-full bg-gradient-to-br from-amber-400 via-zinc-700 to-zinc-900 border-2 border-amber-400/50 shadow-md">
                                    <div class="absolute inset-2 rounded-full bg-zinc-900 border border-amber-500/40"></div>
                                </div>
                                <!-- Arm Rod (Stainless steel finish) -->
                                <div class="absolute top-7 right-6 w-1.5 h-28 bg-gradient-to-r from-zinc-300 via-zinc-400 to-zinc-600 rounded-full shadow-md origin-top transform rotate-18">
                                    <!-- Cartridge / Head-shell with needle -->
                                    <div class="absolute -bottom-4 -left-2 w-6 h-4 bg-amber-500 rounded-sm shadow-md border border-amber-300">
                                        <div class="absolute bottom-0 left-1 w-1 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- The Vintage Vinyl Record Disc -->
                            <div
                                id="vinyl-disc"
                                class="relative w-[92%] aspect-square rounded-full vinyl-grooves vinyl-rotating flex items-center justify-center p-2 shadow-2xl transition-all"
                            >
                                <!-- Interactive Dynamic Specular Light Sheen (Follows Mouse Angle) -->
                                <div id="vinyl-sheen-overlay" class="absolute inset-0 rounded-full vinyl-sheen"></div>

                                <!-- Subtle Concentric Groove Lines (High Fidelity Microgrooves) -->
                                <div class="absolute inset-4 rounded-full border border-white/[0.04]"></div>
                                <div class="absolute inset-8 rounded-full border border-white/[0.05]"></div>
                                <div class="absolute inset-12 rounded-full border border-white/[0.03]"></div>
                                <div class="absolute inset-16 rounded-full border border-white/[0.04]"></div>
                                <div class="absolute inset-20 rounded-full border border-white/[0.03]"></div>
                                <div class="absolute inset-24 rounded-full border border-white/[0.05]"></div>

                                <!-- Center Record Label (Vintage Vinyl Center Sticker) -->
                                <div class="relative size-32 sm:size-36 rounded-full bg-gradient-to-br from-amber-600 via-amber-500 to-amber-700 p-2.5 flex flex-col items-center justify-center text-center shadow-lg border-2 border-amber-300/40 select-none">
                                    <div class="absolute inset-1 rounded-full border border-black/20"></div>

                                    <!-- Label Content -->
                                    <span class="text-[8px] font-mono-vintage font-bold tracking-widest text-black/80 uppercase">QueCantamos</span>
                                    <span class="text-xs sm:text-sm font-serif-vintage italic font-black text-black leading-none my-0.5">Setlist Master</span>
                                    <span class="text-[7px] font-mono-vintage tracking-wider text-black/90 uppercase font-semibold">Side A • 33 ⅓ RPM</span>

                                    <!-- Spindle Center Hole with Brass Ring -->
                                    <div class="my-1.5 size-5 rounded-full bg-[#0d0f12] border-2 border-amber-200/90 shadow-inner flex items-center justify-center">
                                        <div class="size-1.5 rounded-full bg-black"></div>
                                    </div>

                                    <span class="text-[7px] font-mono-vintage text-black/75 tracking-tight font-medium">LADO 1 • EN VIVO</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deck Footer: Analog VU-Meter & Mouse Tracker Indicator -->
                        <div class="mt-3 pt-3 border-t border-white/5 flex items-center justify-between px-2">
                            <!-- Dual Analog VU-Meters that react to mouse speed -->
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center justify-between text-[9px] font-mono-vintage text-zinc-400 mb-1">
                                        <span>VU-L</span>
                                        <span id="vu-db-text" class="text-amber-400 font-bold">-3 dB</span>
                                    </div>
                                    <div class="w-24 sm:w-28 h-2 bg-black/60 rounded-full overflow-hidden p-0.5 border border-white/10 flex items-center">
                                        <div id="vu-bar-l" class="h-full bg-gradient-to-r from-emerald-500 via-amber-400 to-rose-500 rounded-full transition-all duration-75 w-1/3"></div>
                                    </div>
                                </div>
                                <div class="hidden sm:flex flex-col">
                                    <div class="flex items-center justify-between text-[9px] font-mono-vintage text-zinc-400 mb-1">
                                        <span>VU-R</span>
                                        <span>+0 dB</span>
                                    </div>
                                    <div class="w-20 h-2 bg-black/60 rounded-full overflow-hidden p-0.5 border border-white/10 flex items-center">
                                        <div id="vu-bar-r" class="h-full bg-gradient-to-r from-emerald-500 via-amber-400 to-rose-500 rounded-full transition-all duration-75 w-1/2"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] font-mono-vintage text-zinc-500 block">SENSIBILIDAD</span>
                                <span class="text-[11px] font-mono-vintage text-amber-400/90 font-medium">REACTIVO AL MOUSE</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- The 3 Essential Value Pillars (Spotify Premium "Por qué" Style) -->
        <section class="py-20 lg:py-28 border-t border-amber-950/40 relative bg-gradient-to-b from-transparent via-[#121418] to-transparent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="text-xs font-mono-vintage text-amber-400 uppercase tracking-widest">Lo esencial para músicos</span>
                    <h2 class="text-4xl sm:text-5xl font-serif-vintage tracking-tight text-white">
                        Todo lo que necesitas para tu <span class="italic text-amber-400">próximo show</span>
                    </h2>
                    <p class="text-zinc-400 text-base sm:text-lg">
                        Diseñado para resolver los tres problemas reales que enfrenta cualquier banda en directo.
                    </p>
                </div>

                <!-- 3 Pillars Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Pillar 1: Setlists Dinámicos -->
                    <div class="relative rounded-3xl bg-[#15171b]/90 border border-amber-500/20 p-8 shadow-xl hover:border-amber-400/40 transition-all hover:scale-[1.02] flex flex-col group">
                        <div class="size-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-6 group-hover:bg-amber-500 group-hover:text-black transition-colors">
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-serif-vintage text-white mb-3">Setlists en Tiempo Real</h3>
                        <p class="text-zinc-300 text-sm leading-relaxed flex-grow">
                            Ordena canciones en segundos, calcula la duración total del concierto y ajusta el orden sobre la marcha sin rayar hojas de papel.
                        </p>
                        <div class="mt-6 pt-4 border-t border-white/5 font-mono-vintage text-xs text-amber-400/80">
                            ✓ Tiempos & BPM calculados
                        </div>
                    </div>

                    <!-- Pillar 2: Acordes & Tono Sincronizado -->
                    <div class="relative rounded-3xl bg-[#15171b]/90 border border-amber-500/20 p-8 shadow-xl hover:border-amber-400/40 transition-all hover:scale-[1.02] flex flex-col group">
                        <div class="size-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-6 group-hover:bg-amber-500 group-hover:text-black transition-colors">
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-serif-vintage text-white mb-3">Transporte de Acordes</h3>
                        <p class="text-zinc-300 text-sm leading-relaxed flex-grow">
                            ¿El cantante necesita bajar medio tono? Cambia la tonalidad de cualquier canción al instante y léela con tipografía de alto contraste para escenario.
                        </p>
                        <div class="mt-6 pt-4 border-t border-white/5 font-mono-vintage text-xs text-amber-400/80">
                            ✓ Transposición instantánea (+/-)
                        </div>
                    </div>

                    <!-- Pillar 3: Comparte con tu Banda -->
                    <div class="relative rounded-3xl bg-[#15171b]/90 border border-amber-500/20 p-8 shadow-xl hover:border-amber-400/40 transition-all hover:scale-[1.02] flex flex-col group">
                        <div class="size-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-6 group-hover:bg-amber-500 group-hover:text-black transition-colors">
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-serif-vintage text-white mb-3">Comparte en un Clic</h3>
                        <p class="text-zinc-300 text-sm leading-relaxed flex-grow">
                            Envía el enlace del setlist a tu baterista, bajista o sonidista por WhatsApp. Podrán abrirlo y verlo en vivo sin necesidad de crear una cuenta.
                        </p>
                        <div class="mt-6 pt-4 border-t border-white/5 font-mono-vintage text-xs text-amber-400/80">
                            ✓ Vista pública sin login requerido
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Vintage Live Setlist Cue-Sheet Showcase -->
        <section class="py-16 lg:py-24 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-4xl mx-auto rounded-3xl bg-[#16181d] border border-amber-500/25 p-6 sm:p-10 shadow-2xl relative overflow-hidden">
                <!-- Warm paper texture tone -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-6 border-b border-amber-500/20 gap-4">
                    <div>
                        <span class="text-[11px] font-mono-vintage text-amber-400 uppercase tracking-widest block mb-1">SETLIST EN VIVO • VISTA PREVIA</span>
                        <h3 class="text-2xl sm:text-3xl font-serif-vintage text-white">Noche de Clásicos — Gira 2026</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 font-mono-vintage text-xs">
                            5 Canciones • 22 Min
                        </span>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono-vintage text-xs flex items-center gap-1.5">
                            <span class="size-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>EN DIRECTO</span>
                        </span>
                    </div>
                </div>

                <!-- Setlist tracks -->
                <div class="divide-y divide-white/5 mt-4">
                    <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="font-mono-vintage text-amber-400 font-bold text-sm w-6">01</span>
                            <div>
                                <span class="font-bold text-white text-base">De Música Ligera</span>
                                <span class="text-xs text-zinc-400 block font-mono-vintage">Soda Stereo</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-mono-vintage">
                            <span class="px-2 py-0.5 rounded bg-black/40 text-amber-300 border border-amber-500/20">Bm</span>
                            <span class="text-zinc-400 hidden sm:inline">128 BPM</span>
                            <span class="text-zinc-400 font-medium">3:40</span>
                        </div>
                    </div>

                    <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="font-mono-vintage text-amber-400 font-bold text-sm w-6">02</span>
                            <div>
                                <span class="font-bold text-white text-base">Flaca</span>
                                <span class="text-xs text-zinc-400 block font-mono-vintage">Andrés Calamaro</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-mono-vintage">
                            <span class="px-2 py-0.5 rounded bg-black/40 text-amber-300 border border-amber-500/20">G</span>
                            <span class="text-zinc-400 hidden sm:inline">116 BPM</span>
                            <span class="text-zinc-400 font-medium">4:15</span>
                        </div>
                    </div>

                    <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="font-mono-vintage text-amber-400 font-bold text-sm w-6">03</span>
                            <div>
                                <span class="font-bold text-white text-base">Persiana Americana</span>
                                <span class="text-xs text-zinc-400 block font-mono-vintage">Soda Stereo</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-mono-vintage">
                            <span class="px-2 py-0.5 rounded bg-black/40 text-amber-300 border border-amber-500/20">Am</span>
                            <span class="text-zinc-400 hidden sm:inline">132 BPM</span>
                            <span class="text-zinc-400 font-medium">4:50</span>
                        </div>
                    </div>

                    <div class="py-3.5 flex items-center justify-between hover:bg-white/[0.02] px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="font-mono-vintage text-amber-400 font-bold text-sm w-6">04</span>
                            <div>
                                <span class="font-bold text-white text-base">Trátame Suavemente</span>
                                <span class="text-xs text-zinc-400 block font-mono-vintage">Los Encargados</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-mono-vintage">
                            <span class="px-2 py-0.5 rounded bg-black/40 text-amber-300 border border-amber-500/20">E</span>
                            <span class="text-zinc-400 hidden sm:inline">95 BPM</span>
                            <span class="text-zinc-400 font-medium">3:22</span>
                        </div>
                    </div>
                </div>

                <!-- Call to action inside the preview -->
                <div class="mt-8 pt-6 border-t border-amber-500/20 flex flex-col sm:flex-row items-center justify-between gap-4 bg-amber-500/5 -mx-6 -mb-6 sm:-mx-10 sm:-mb-10 p-6 sm:p-8 rounded-b-3xl">
                    <span class="text-sm text-zinc-300 text-center sm:text-left">
                        ¿Listo para crear el setlist de tu banda? Toma menos de 2 minutos.
                    </span>
                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="px-6 py-2.5 rounded-full bg-amber-500 hover:bg-amber-400 text-black font-bold text-sm transition-all shadow-md shadow-amber-500/20 shrink-0"
                        >
                            Comenzar gratis
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <!-- Final CTA Banner (Spotify Premium Style) -->
        <section class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 border-t border-amber-950/40 text-center relative overflow-hidden">
            <div class="max-w-4xl mx-auto space-y-6">
                <span class="text-xs font-mono-vintage text-amber-400 uppercase tracking-widest block">EMPIEZA HOY MISMO</span>
                <h2 class="text-4xl sm:text-6xl font-serif-vintage tracking-tight text-white leading-tight">
                    Haz que tu música suene <br>
                    <span class="italic text-amber-400">exactamente como debe ser.</span>
                </h2>
                <p class="text-zinc-300 max-w-xl mx-auto text-base sm:text-lg">
                    Sin suscripciones forzadas ni tarjetas de crédito. Diseñado por y para músicos que tocan en vivo.
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="w-full sm:w-auto px-9 py-4 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-black font-extrabold text-base tracking-wide transition-all shadow-xl shadow-amber-500/25 hover:scale-[1.03]"
                        >
                            Crear cuenta gratis
                        </a>
                    @endif

                    @if (Route::has('login'))
                        <a
                            href="{{ route('login') }}"
                            class="w-full sm:w-auto px-8 py-4 rounded-full bg-white/5 hover:bg-white/10 text-white font-semibold text-base border border-white/15 transition-all"
                        >
                            Iniciar sesión
                        </a>
                    @endif
                </div>
            </div>
        </section>

    </main>

    <!-- Minimalist Vintage Footer -->
    <footer class="relative z-10 border-t border-white/10 bg-[#0a0b0d] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-6 text-xs text-zinc-400 font-mono-vintage">
            <div class="flex items-center gap-3">
                <div class="flex aspect-square size-7 items-center justify-center rounded-lg bg-amber-500 text-black p-0.5">
                    <x-app-logo-icon class="size-5 text-black" />
                </div>
                <span class="text-sm font-black text-white">quecantamos<span class="text-amber-500">.cl</span></span>
                <span class="text-zinc-400">© {{ date('Y') }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">Iniciar sesión</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="hover:text-amber-400 transition-colors">Crear cuenta</a>
                @endif
                <span class="text-zinc-400">Hecho para músicos en vivo</span>
            </div>
        </div>
    </footer>

    <!-- Interactive Mouse-Reactive & Vinyl Physics JavaScript -->
    <script>
        (function() {
            // Elements
            const heroContainer = document.getElementById('hero-container');
            const deck = document.getElementById('vinyl-deck');
            const vinylDisc = document.getElementById('vinyl-disc');
            const sheenOverlay = document.getElementById('vinyl-sheen-overlay');
            const floatingLayer = document.getElementById('floating-layer');
            const tonearm = document.getElementById('tonearm');
            const vuBarL = document.getElementById('vu-bar-l');
            const vuBarR = document.getElementById('vu-bar-r');
            const vuText = document.getElementById('vu-db-text');

            // Interactive controls
            const btnRpm33 = document.getElementById('btn-rpm-33');
            const btnRpm45 = document.getElementById('btn-rpm-45');
            const btnPlayToggle = document.getElementById('btn-play-toggle');

            if (!deck || !vinylDisc) return;

            // Physics state
            let mouseX = 0, mouseY = 0;
            let targetMouseX = 0, targetMouseY = 0;
            let lastMouseX = 0, lastMouseY = 0;
            let mouseSpeed = 0;
            let isPlaying = true;
            let currentRPM = 33.3;

            // Handle Mouse Move
            function onMouseMove(e) {
                const rect = heroContainer.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;

                // Normalized mouse coordinates from -1 to 1
                targetMouseX = Math.max(-1, Math.min(1, (e.clientX - centerX) / (rect.width / 2)));
                targetMouseY = Math.max(-1, Math.min(1, (e.clientY - centerY) / (rect.height / 2)));

                // Calculate instantaneous mouse speed for VU meter
                const dx = e.clientX - lastMouseX;
                const dy = e.clientY - lastMouseY;
                mouseSpeed = Math.sqrt(dx * dx + dy * dy);
                lastMouseX = e.clientX;
                lastMouseY = e.clientY;
            }

            // Window mouse listener
            window.addEventListener('mousemove', onMouseMove, { passive: true });

            // Touch support for mobile: simulate slight interaction or touchmove
            window.addEventListener('touchmove', function(e) {
                if (e.touches.length > 0) {
                    const touch = e.touches[0];
                    const rect = heroContainer.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    targetMouseX = Math.max(-1, Math.min(1, (touch.clientX - centerX) / (rect.width / 2)));
                    targetMouseY = Math.max(-1, Math.min(1, (touch.clientY - centerY) / (rect.height / 2)));
                }
            }, { passive: true });

            // Animation Loop (60 FPS / requestAnimationFrame)
            let animationFrameId;
            let time = 0;

            function updatePhysics() {
                time += 0.02;

                // Smooth Linear Interpolation (lerp)
                mouseX += (targetMouseX - mouseX) * 0.08;
                mouseY += (targetMouseY - mouseY) * 0.08;

                // Decay mouse speed gradually
                mouseSpeed *= 0.92;

                // 3D Parallax Tilt for the turntable deck
                const tiltX = -mouseY * 16; // Degrees of rotation around X axis
                const tiltY = mouseX * 20;  // Degrees of rotation around Y axis
                deck.style.transform = `rotateX(${tiltX.toFixed(2)}deg) rotateY(${tiltY.toFixed(2)}deg)`;

                // Dynamic light sheen rotation on vinyl grooves based on mouse angle
                const sheenAngle = (Math.atan2(mouseY, mouseX) * 180 / Math.PI) + 90;
                sheenOverlay.style.setProperty('--sheen-angle', `${sheenAngle.toFixed(1)}deg`);

                // Parallax depth for floating musical chords and notes
                if (floatingLayer) {
                    const floatX = mouseX * 24;
                    const floatY = mouseY * 20;
                    floatingLayer.style.transform = `translate3d(${floatX.toFixed(1)}px, ${floatY.toFixed(1)}px, 40px)`;
                }

                // Dynamic VU-Meter needles based on mouse movement activity
                const baseVU = isPlaying ? 28 : 5;
                const activityVU = Math.min(68, mouseSpeed * 1.8);
                const totalVULeft = Math.min(95, Math.max(10, baseVU + activityVU + Math.sin(time * 3) * 6));
                const totalVURight = Math.min(95, Math.max(8, baseVU + activityVU * 0.9 + Math.cos(time * 2.5) * 8));

                if (vuBarL) vuBarL.style.width = `${totalVULeft}%`;
                if (vuBarR) vuBarR.style.width = `${totalVURight}%`;

                if (vuText) {
                    const dbVal = Math.round((totalVULeft / 100) * 12 - 9);
                    vuText.textContent = `${dbVal >= 0 ? '+' : ''}${dbVal} dB`;
                }

                animationFrameId = requestAnimationFrame(updatePhysics);
            }

            // Start physics loop
            updatePhysics();

            // Toggle play/pause on vinyl or deck click
            function togglePlayState() {
                isPlaying = !isPlaying;
                if (isPlaying) {
                    vinylDisc.classList.remove('vinyl-paused');
                    if (tonearm) tonearm.style.transform = 'rotate(-12deg)';
                    if (btnPlayToggle) {
                        btnPlayToggle.textContent = 'Pausa';
                        btnPlayToggle.classList.remove('bg-rose-500', 'text-white');
                        btnPlayToggle.classList.add('text-zinc-400');
                    }
                } else {
                    vinylDisc.classList.add('vinyl-paused');
                    if (tonearm) tonearm.style.transform = 'rotate(-32deg)';
                    if (btnPlayToggle) {
                        btnPlayToggle.textContent = 'Girar';
                        btnPlayToggle.classList.add('bg-amber-500', 'text-black');
                        btnPlayToggle.classList.remove('text-zinc-400');
                    }
                }
            }

            deck.addEventListener('click', togglePlayState);

            if (btnPlayToggle) {
                btnPlayToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    togglePlayState();
                });
            }

            // Speed Buttons: 33 ⅓ RPM vs 45 RPM
            if (btnRpm33 && btnRpm45) {
                btnRpm33.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentRPM = 33.3;
                    vinylDisc.style.setProperty('--spin-duration', '2.2s');
                    btnRpm33.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-black transition-colors';
                    btnRpm45.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold text-zinc-400 hover:text-white transition-colors';
                });

                btnRpm45.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentRPM = 45;
                    vinylDisc.style.setProperty('--spin-duration', '1.3s');
                    btnRpm45.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-black transition-colors';
                    btnRpm33.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold text-zinc-400 hover:text-white transition-colors';
                });
            }

        })();
    </script>
</body>
</html>
