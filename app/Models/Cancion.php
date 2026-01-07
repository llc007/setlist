<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancion extends Model
{
    protected $table = 'canciones';

    protected $fillable = [
        'titulo',
        'artista',
        'letra',
        'tono_original',
        'categoria_id',
    ];

    /**
     * Una canción puede tener muchos recursos (PDF, YouTube, etc.).
     */
    public function recursos(): HasMany
    {
        return $this->hasMany(CancionRecurso::class, 'cancion_id');
    }

    /**
     * La canción pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
