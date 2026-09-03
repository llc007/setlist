<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancion extends Model
{
    use HasFactory;

    protected $table = 'canciones';

    protected $fillable = [
        'titulo',
        'artista',
        'letra',
        'tono_original',
        'categoria_id',
        'banda_id',
        'es_publica',
        'ambito',
        'codigo',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'es_publica' => 'boolean',
        ];
    }

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

    /**
     * Banda creadora (si fue subida por una banda específica).
     */
    public function banda(): BelongsTo
    {
        return $this->belongsTo(Banda::class, 'banda_id');
    }

    /**
     * Bandas que tienen esta canción en su repertorio.
     */
    public function bandasQueLaTienen(): BelongsToMany
    {
        return $this->belongsToMany(Banda::class, 'banda_cancion')
            ->withTimestamps();
    }
}
