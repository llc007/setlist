<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cancion;
use App\Models\User;

class DasboardController extends Controller
{
    public function index()
    {
        return view('admin', [
            'totalCanciones' => Cancion::all()->count(),
            'totalMiembros' => User::all()->count(), // Ejemplo
            // 'proximosServicios' => \App\Models\Servicio::proximos()->get(), // Ejemplo
        ]);
    }
}
