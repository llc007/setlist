<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Setlist extends Model
{
    use HasFactory;

    protected $table = 'setlists';

    protected $fillable = [
        'banda_id',
        'user_id',
        'nombre',
        'descripcion',
        'fecha',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /**
     * Banda a la que pertenece el setlist.
     */
    public function banda(): BelongsTo
    {
        return $this->belongsTo(Banda::class, 'banda_id');
    }

    /**
     * Creador del setlist.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Canciones que conforman el setlist con orden y notas.
     */
    public function canciones(): BelongsToMany
    {
        return $this->belongsToMany(Cancion::class, 'cancion_setlist')
            ->withPivot(['id', 'orden', 'proposito', 'tono', 'observacion', 'nota'])
            ->orderBy('cancion_setlist.orden', 'asc')
            ->withTimestamps();
    }
}
