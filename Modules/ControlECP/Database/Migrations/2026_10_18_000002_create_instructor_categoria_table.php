<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para relacionar instructores con categorías de asesoría.
return new class extends Migration
{
    /**
     * Tabla instructor_categoria
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de instructor_categoria.
        Schema::create('instructor_categoria', function (Blueprint $table) {
            // Identificador y claves de instructor y categoría asociada.
            $table->id('id_instructor_categoria');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_categoria_asesoria');

            // Las claves mantienen las asociaciones y se eliminan junto a su entidad; la pareja no se repite.
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_categoria_asesoria')->references('id_categoria_asesoria')->on('categorias_asesoria')->onDelete('cascade');
            $table->unique(['id_usuario', 'id_categoria_asesoria']);
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('instructor_categoria');
    }
};
