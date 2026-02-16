<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'slug',
    ];

    /**
     * Una categoría puede tener muchas canciones.
     */
    public function canciones(): HasMany
    {
        return $this->hasMany(Cancion::class, 'categoria_id');
    }
}
