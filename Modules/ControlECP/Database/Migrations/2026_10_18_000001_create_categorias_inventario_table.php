<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de Categorías de Inventario para Control ECP.
     */
    public function up(): void
    {
        Schema::create('categorias_inventario', function (Blueprint $table) {
            $table->id('id_categoria_inventario');
            $table->string('nombre');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_inventario');
    }
};
