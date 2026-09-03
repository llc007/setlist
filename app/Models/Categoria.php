<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'parent_id',
        'nombre',
        'slug',
        'ambito',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Categoria $categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });

        static::updating(function (Categoria $categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });
    }

    /**
     * Categoría padre.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    /**
     * Subcategorías hijas.
     */
    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    /**
     * Una categoría puede tener muchas canciones.
     */
    public function canciones(): HasMany
    {
        return $this->hasMany(Cancion::class, 'categoria_id');
    }
}
