<x-layouts.app :title="__('Admin Dashboard')">

    <div class="flex-1 w-full max-w-7xl mx-auto p-4 md:p-8 lg:p-12 flex flex-col gap-8">
        <header class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Bienvenida,
                    Sara</h2>
                <p class="text-slate-500 dark:text-text-secondary text-base font-normal">Resumen para este domingo, 24
                    de
                    Oct</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    class="p-2 rounded-full bg-white dark:bg-card-dark text-slate-500 hover:text-primary border border-slate-200 dark:border-slate-700 shadow-sm transition-colors"
                    title="Alternar tema oscuro">
                    <span class="material-symbols-outlined text-[20px]">dark_mode</span>
                </button>
                <span
                    class="px-3 py-1 rounded-full bg-green-500/10 text-green-600 dark:text-green-500 text-sm font-medium border border-green-500/20 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Sistema Online
                </span>
            </div>
        </header>

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                class="flex flex-col gap-3 rounded-xl p-5 bg-white dark:bg-card-dark shadow-sm border border-slate-200 dark:border-slate-800 transition-transform hover:-translate-y-1 duration-300">
                <div class="flex items-center justify-between">
                    <p class="text-slate-500   dark:text-text-secondary text-sm font-medium uppercase tracking-wider">
                        Total Canciones</p>
                    <span class="material-symbols-outlined text-primary">library_music</span>
                </div>
                <div class="flex items-end gap-3">
                    <p class="text-slate-900 dark:text-white text-3xl font-bold leading-none">342</p>
                    <p class="text-emerald-500 text-sm font-bold bg-emerald-500/10 px-2 py-0.5 rounded">+12%</p>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 rounded-xl p-5 bg-white dark:bg-card-dark shadow-sm border border-slate-200 dark:border-slate-800 transition-transform hover:-translate-y-1 duration-300">
                <div class="flex items-center justify-between">
                    <p class="text-slate-500 dark:text-text-secondary text-sm font-medium uppercase tracking-wider">
                        Miembros Activos</p>
                    <span class="material-symbols-outlined text-primary">groups</span>
                </div>
                <div class="flex items-end gap-3">
                    <p class="text-slate-900 dark:text-white text-3xl font-bold leading-none">45</p>
                    <p class="text-emerald-500 text-sm font-bold bg-emerald-500/10 px-2 py-0.5 rounded">+2%</p>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 rounded-xl p-5 bg-white dark:bg-card-dark shadow-sm border border-slate-200 dark:border-slate-800 transition-transform hover:-translate-y-1 duration-300">
                <div class="flex items-center justify-between">
                    <p class="text-slate-500 dark:text-text-secondary text-sm font-medium uppercase tracking-wider">
                        Servicios (Mes)</p>
                    <span class="material-symbols-outlined text-primary">calendar_month</span>
                </div>
                <div class="flex items-end gap-3">
                    <p class="text-slate-900 dark:text-white text-3xl font-bold leading-none">8</p>
                    <p class="text-slate-400 text-sm font-medium">En camino</p>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-slate-900 dark:text-white text-lg font-bold mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button
                    class="flex items-center justify-center gap-3 p-4 rounded-xl bg-primary hover:bg-primary-hover text-white font-semibold transition-all shadow-lg shadow-primary/25 group">
                    <span
                        class="material-symbols-outlined group-hover:scale-110 transition-transform">playlist_add</span>
                    Crear Setlist
                </button>
                <button
                    class="flex items-center justify-center gap-3 p-4 rounded-xl bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-card-dark-hover border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all group">
                    <span
                        class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">add_circle</span>
                    Añadir Canción
                </button>
                <button
                    class="flex items-center justify-center gap-3 p-4 rounded-xl bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-card-dark-hover border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-medium transition-all group">
                    <span
                        class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">mail</span>
                    Mensaje al Coro
                </button>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 flex flex-col gap-8">
                <section class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-slate-900 dark:text-white text-lg font-bold">Próximos Servicios</h3>
                        <a class="text-primary text-sm font-medium hover:underline" href="#">Ver Calendario</a>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row gap-5 p-5 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                        <div
                            class="flex flex-col items-center justify-center min-w-[80px] text-center p-3 rounded-lg bg-slate-100 dark:bg-slate-800/50">
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase">Oct</span>
                            <span class="text-slate-900 dark:text-white text-2xl font-black">24</span>
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-medium">Dom</span>
                        </div>
                        <div class="flex-1 flex flex-col gap-2 justify-center">
                            <h4
                                class="text-slate-900 dark:text-white text-lg font-bold group-hover:text-primary transition-colors">
                                Adoración Dominical
                            </h4>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full" style="width: 80%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span>4/5 Canciones</span>
                                <span>80% Completo</span>
                            </div>
                        </div>
                        <button
                            class="self-center p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row gap-5 p-5 rounded-xl bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-slate-500"></div>
                        <div
                            class="flex flex-col items-center justify-center min-w-[80px] text-center p-3 rounded-lg bg-slate-100 dark:bg-slate-800/50">
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase">Oct</span>
                            <span class="text-slate-900 dark:text-white text-2xl font-black">27</span>
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-medium">Mié</span>
                        </div>
                        <div class="flex-1 flex flex-col gap-2 justify-center">
                            <h4
                                class="text-slate-900 dark:text-white text-lg font-bold group-hover:text-primary transition-colors">
                                Ensayo General
                            </h4>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span>2/2 Canciones</span>
                                <span class="text-green-500 font-medium">Listo</span>
                            </div>
                        </div>
                        <button
                            class="self-center p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-900 dark:text-white text-lg font-bold">Canciones Recientes</h3>
                        <a class="text-primary text-sm font-medium hover:underline" href="#">Ver Biblioteca</a>
                    </div>
                    <div
                        class="bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div
                                class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-card-dark-hover transition-colors cursor-pointer group">
                                <div class="size-12 rounded-lg bg-cover bg-center shrink-0"
                                    data-alt="abstract album art blue and purple"
                                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCSdpOyYTgxMtTmHuSoh7Z211OD8sSoyO6s0KAwzhurNDIFGiviStzuTwVzS3XWcQLpAD1aa0u2rjoTTTRqr4smku2kSjhbmfVgCT1vJ_XYFv_nbQc5JeH-b2vTqRtqkaSltJDcngPYlmT-SnRmqWp5AAhlvrzu8C_LmkbaEqINj9EaDSdz1dR-ZIW92W2wu_2lrdQ1ahAaFONoGzeEQk1auqSoiW1A4NKji6TNUEBLnKgm7JkXUP9npjd9jSiUULs6GhMPhdR5Zcwx");'>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-slate-900 dark:text-white font-medium truncate group-hover:text-primary transition-colors">
                                        Oceans (Where Feet May Fail)
                                    </p>
                                    <p class="text-slate-500 text-sm truncate">Hillsong United • Tono: Re</p>
                                </div>
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium bg-blue-500/10 text-blue-500 border border-blue-500/20 whitespace-nowrap">Contemporáneo</span>
                            </div>
                            <div
                                class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-card-dark-hover transition-colors cursor-pointer group">
                                <div class="size-12 rounded-lg bg-cover bg-center shrink-0"
                                    data-alt="abstract album art warm orange"
                                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuB6P9P6hzY1DtqEOp6ZubazxxivnNr12TYkGSEiFoZs8XwmvWdka8HY5ihAlHk6ZEosf0sQ_vp-TRIBnU0tD8cSt9LcoaVb5YgaFUegtvp4FWeaGshEVo9k5Ae-RsWxpMqJEX2DmSovwJWfHGEtmTA_RcbEtF31LqR7STWBm4YNU8TaRdLNLqpxAN7ySLvbDEpAYPmVZdRF1M0NIuj9IfWhD6m5_U73VxY_5OYdY7dLpJRcRHeVDAqc4ysKyZ3da6nE-djNmET-_OWY");'>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-slate-900 dark:text-white font-medium truncate group-hover:text-primary transition-colors">
                                        Sublime Gracia</p>
                                    <p class="text-slate-500 text-sm truncate">Tradicional • Tono: Sol</p>
                                </div>
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium bg-purple-500/10 text-purple-500 border border-purple-500/20 whitespace-nowrap">Himno</span>
                            </div>
                            <div
                                class="p-4 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-card-dark-hover transition-colors cursor-pointer group">
                                <div class="size-12 rounded-lg bg-cover bg-center shrink-0"
                                    data-alt="abstract album art dark aesthetic"
                                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCXK_BtiEGTYb8u1-z-WtSOYhjIGy-MtGT2x0nCeXze4KXkjxHDIosypq8lf5GiAuuRxBUKbBPI_KHlYH1Rm93mAQJZLgPenlbc_Gm3MzZWLte5JmiAm0-IpimFdpNG-TMzq2hczgW4ezpHzHo89t5kapoIvexw5622DbphopiWPkitOgZNMDlM-vjVU1GyYLSQp8B-oELLp5mZZU57WB0WlqwfJcAQfvQPVyFg20bK-nvnxUwZhknhg8_fyJtWCukXBiv6P0Gto5ru");'>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-slate-900 dark:text-white font-medium truncate group-hover:text-primary transition-colors">
                                        Way Maker</p>
                                    <p class="text-slate-500 text-sm truncate">Sinach • Tono: Mi</p>
                                </div>
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium bg-blue-500/10 text-blue-500 border border-blue-500/20 whitespace-nowrap">Contemporáneo</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-6">
                <div
                    class="bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-xl p-5 h-fit">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-slate-900 dark:text-white text-lg font-bold">Notificaciones</h3>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">2</span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex gap-3">
                            <div class="mt-1">
                                <span class="material-symbols-outlined text-amber-500 text-xl">person_add</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-slate-900 dark:text-white text-sm font-medium">Solicitud de Acceso</p>
                                <p class="text-slate-500 text-sm leading-snug">3 miembros del coro solicitaron acceso
                                    al portal.</p>
                                <div class="flex gap-3 mt-1">
                                    <button class="text-primary text-xs font-semibold hover:underline">Revisar</button>
                                    <button
                                        class="text-slate-500 text-xs font-semibold hover:text-slate-300">Descartar</button>
                                </div>
                            </div>
                        </div>
                        <div class="h-px bg-slate-100 dark:bg-slate-700 w-full"></div>
                        <div class="flex gap-3">
                            <div class="mt-1">
                                <span class="material-symbols-outlined text-blue-500 text-xl">schedule</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-slate-900 dark:text-white text-sm font-medium">Actualización de Horario
                                </p>
                                <p class="text-slate-500 text-sm leading-snug">Ensayo del 1 de Nov cambiado a las
                                    19:00.</p>
                            </div>
                        </div>
                        <div class="h-px bg-slate-100 dark:bg-slate-700 w-full"></div>
                        <div class="flex gap-3 opacity-60 hover:opacity-100 transition-opacity">
                            <div class="mt-1">
                                <span class="material-symbols-outlined text-slate-400 text-xl">check_circle</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-slate-900 dark:text-white text-sm font-medium">Lista Aprobada</p>
                                <p class="text-slate-500 text-sm leading-snug">El Pastor Marcos aprobó la lista del 24
                                    de Oct.</p>
                                <span class="text-slate-600 dark:text-slate-500 text-xs">hace 2 horas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-primary/20 to-purple-500/10 rounded-xl p-5 border border-primary/10">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2 text-primary font-bold">
                            <span class="material-symbols-outlined">lightbulb</span>
                            <span>Consejo del día</span>
                        </div>
                        <p class="text-slate-900 dark:text-white text-sm font-medium">¿Transponiendo acordes?</p>
                        <p class="text-slate-500 dark:text-text-secondary text-sm">
                            Usa la función "Transposición Inteligente" en la pestaña de Canciones para actualizar
                            automáticamente las partituras.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
