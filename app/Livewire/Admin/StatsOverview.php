<?php

namespace App\Livewire\Admin;

use App\Models\Cancion;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsOverview extends Component
{
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
        $this->totalServicios = 8; // Podrás hacerlo dinámico luego
    }

    public function render()
    {
        return view('livewire.admin.stats-overview');
    }
}
