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
        // 1. Tablas de Bandas
        Schema::create('bandas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // 2. Pivot Banda - User (Miembros)
        Schema::create('banda_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banda_id')->constrained('bandas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rol')->default('Integrante de Banda'); // 'Administrador de Banda', 'Integrante de Banda'
            $table->timestamps();

            $table->unique(['banda_id', 'user_id']);
        });

        // 3. Modificación a Canciones para soporte de Banda / Visibilidad
        Schema::table('canciones', function (Blueprint $table) {
            $table->foreignId('banda_id')->nullable()->after('categoria_id')->constrained('bandas')->nullOnDelete();
            $table->boolean('es_publica')->default(true)->after('banda_id');
        });

        // 4. Pivot Banda - Cancion (Repertorio adjunto de la banda)
        Schema::create('banda_cancion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banda_id')->constrained('bandas')->cascadeOnDelete();
            $table->foreignId('cancion_id')->constrained('canciones')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['banda_id', 'cancion_id']);
        });

        // 5. Tabla Setlists
        Schema::create('setlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banda_id')->constrained('bandas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->string('tipo')->default('culto'); // 'ensayo', 'presentacion', 'culto', 'otro'
            $table->timestamps();
        });

        // 6. Pivot Cancion - Setlist
        Schema::create('cancion_setlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setlist_id')->constrained('setlists')->cascadeOnDelete();
            $table->foreignId('cancion_id')->constrained('canciones')->cascadeOnDelete();
            $table->integer('orden')->default(0);
            $table->text('nota')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancion_setlist');
        Schema::dropIfExists('setlists');
        Schema::dropIfExists('banda_cancion');

        Schema::table('canciones', function (Blueprint $table) {
            $table->dropForeign(['banda_id']);
            $table->dropColumn(['banda_id', 'es_publica']);
        });

        Schema::dropIfExists('banda_user');
        Schema::dropIfExists('bandas');
    }
};
