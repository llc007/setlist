<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DasboardController extends Controller
{
    public function index()
    {
        return view('admin', [
            'totalCanciones' => \App\Models\Cancion::all()->count(),
            'totalMiembros' => \App\Models\User::all()->count(), // Ejemplo
            // 'proximosServicios' => \App\Models\Servicio::proximos()->get(), // Ejemplo
        ]);
    }
}
