<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Banda extends Model
{
    use HasFactory;

    protected $table = 'bandas';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Banda $banda) {
            if (empty($banda->slug)) {
                $banda->slug = Str::slug($banda->nombre);
            }
        });
    }

    /**
     * Usuarios pertenecientes a la banda.
     */
    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'banda_user')
            ->withPivot('rol')
            ->withTimestamps();
    }

    /**
     * Canciones asociadas al repertorio de esta banda (canciones de la biblioteca vinculadas a la banda).
     */
    public function repertorio(): BelongsToMany
    {
        return $this->belongsToMany(Cancion::class, 'banda_cancion')
            ->withTimestamps();
    }

    /**
     * Canciones creadas directamente por esta banda.
     */
    public function cancionesPropias(): HasMany
    {
        return $this->hasMany(Cancion::class, 'banda_id');
    }

    /**
     * Setlists pertenecientes a esta banda.
     */
    public function setlists(): HasMany
    {
        return $this->hasMany(Setlist::class, 'banda_id');
    }
}
