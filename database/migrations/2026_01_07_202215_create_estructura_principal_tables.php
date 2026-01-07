<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    // 1. Categorías
    Schema::create('categorias', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('slug')->unique();
        $table->timestamps();
    });

    // 2. Canciones
    Schema::create('canciones', function (Blueprint $table) {
        $table->id();
        $table->string('titulo');
        $table->string('artista')->nullable();
        $table->text('letra')->nullable();
        $table->string('tono_original')->nullable(); 
        // Especificamos la tabla 'categorias' porque Laravel no sabrá que el plural de categoria es categorias
        $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
        $table->timestamps();
    });

    // 3. Recursos de Canciones
    Schema::create('canciones_recursos', function (Blueprint $table) {
        $table->id();
        // Especificamos la tabla 'canciones'
        $table->foreignId('cancion_id')->constrained('canciones')->cascadeOnDelete();
        $table->string('tipo'); // 'pdf', 'youtube', 'acordes', 'link'
        $table->string('url');  
        $table->string('etiqueta')->nullable(); 
        $table->timestamps();
    });
}

/**
 * El orden es inverso al de creación: 
 * Primero borramos la que tiene las llaves foráneas (recursos), luego canciones y al final categorías.
 */
public function down(): void
{
    Schema::dropIfExists('canciones_recursos');
    Schema::dropIfExists('canciones');
    Schema::dropIfExists('categorias');
}
};
