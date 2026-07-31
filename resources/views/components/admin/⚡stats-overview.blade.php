<?php

use App\Models\Cancion;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $totalCanciones;
    public $totalMiembros;
    public $totalServicios;

    public function mount()
    {
        $this->loadStats();
    }

    #[On('cancion-creada')]
    public function loadStats()
    {
        $this->totalCanciones = Cancion::count();
        $this->totalMiembros = User::count();
        $this->totalServicios = 8;
    }
};
?>

<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <x-stat-card title="Total Canciones" :value="$totalCanciones" icon="library_music" :route="route('admin.canciones')" trend="+1" />

    <x-stat-card title="Miembros Activos" :value="$totalMiembros" icon="groups" trend="+2%" />

    <x-stat-card title="Servicios (Mes)" :value="$totalServicios" icon="calendar_month" trend="En camino" trendColor="slate" />
</section>
