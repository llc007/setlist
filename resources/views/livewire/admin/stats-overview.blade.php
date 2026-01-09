<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <x-stat-card title="Total Canciones" :value="$totalCanciones" icon="library_music" :route="route('admin.canciones')" trend="+1" />

    <x-stat-card title="Miembros Activos" :value="$totalMiembros" icon="groups" trend="+2%" />

    <x-stat-card title="Servicios (Mes)" :value="$totalServicios" icon="calendar_month" trend="En camino" trendColor="slate" />
</section>
