<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancionRecurso extends Model
{
    protected $table = 'canciones_recursos';

    protected $fillable = [
        'cancion_id',
        'tipo',
        'url',
        'etiqueta',
    ];

    /**
     * El recurso pertenece a una canción.
     */
    public function cancion(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'cancion_id');
    }
}
