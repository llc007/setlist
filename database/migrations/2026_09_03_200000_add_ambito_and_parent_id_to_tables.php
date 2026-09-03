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
        // 1. Añadir parent_id y ámbito a categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categorias')->nullOnDelete();
            $table->string('ambito')->default('cristiano')->after('slug'); // 'cristiano', 'secular', 'ambos'
        });

        // 2. Añadir ámbito a canciones
        Schema::table('canciones', function (Blueprint $table) {
            $table->string('ambito')->default('cristiano')->after('es_publica'); // 'cristiano', 'secular'
        });

        // 3. Añadir tipo_ambito a bandas
        Schema::table('bandas', function (Blueprint $table) {
            $table->string('tipo_ambito')->default('cristiano')->after('descripcion'); // 'cristiano', 'secular', 'mixto'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bandas', function (Blueprint $table) {
            $table->dropColumn('tipo_ambito');
        });

        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn('ambito');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'ambito']);
        });
    }
};
