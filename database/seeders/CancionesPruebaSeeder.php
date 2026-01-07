<?php

namespace Database\Seeders;

use App\Models\Cancion;
use App\Models\CancionRecurso;
use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CancionesPruebaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Categorías base
        $catHimnario = Categoria::create(['nombre' => 'Himnario', 'slug' => 'himnario']);
        $catEspeciales = Categoria::create(['nombre' => 'Especiales', 'slug' => 'especiales']);
        $catCoritos = Categoria::create(['nombre' => 'Coritos', 'slug' => 'coritos']);
        $catAdoracion = Categoria::create(['nombre' => 'Adoración', 'slug' => 'adoracion']);

        // 2. Crear una canción de ejemplo (Cuan Grande es Él)
        $cancion1 = Cancion::create([
            'titulo' => 'Cuan Grande es Él',
            'artista' => 'Hymn',
            'letra' => 'Señor mi Dios, al contemplar los cielos...',
            'tono_original' => 'G',
            'categoria_id' => $catCoritos->id,
        ]);

        // 3. Agregar recursos a esa canción
        CancionRecurso::create([
            'cancion_id' => $cancion1->id,
            'tipo' => 'pdf',
            'url' => 'https://ejemplo.com/partitura.pdf',
            'etiqueta' => 'Acordes Piano',
        ]);

        CancionRecurso::create([
            'cancion_id' => $cancion1->id,
            'tipo' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=vvv111',
            'etiqueta' => 'Versión En Vivo',
        ]);

        // 4. Crear otra canción (Vienen con Alegría)
        $cancion2 = Cancion::create([
            'titulo' => 'Vienen con Alegría',
            'artista' => 'Cesáreo Gabaráin',
            'letra' => 'Vienen con alegría Señor, cantando vienen con alegría...',
            'tono_original' => 'D',
            'categoria_id' => $catHimnario->id,
        ]);

        // Recurso para la segunda canción
        CancionRecurso::create([
            'cancion_id' => $cancion2->id,
            'tipo' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=zzz222',
            'etiqueta' => 'Audio Original',
        ]);
    }
}
