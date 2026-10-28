<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear el catálogo de categorías de asesoría.
return new class extends Migration
{
    /**
     * Tabla categorias_asesoria
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de categorias_asesoria.
        Schema::create('categorias_asesoria', function (Blueprint $table) {
            // Identificador, nombre irrepetible y estado de disponibilidad.
            $table->id('id_categoria_asesoria');
            $table->string('nombre')->unique();
            $table->enum('estado', ['Activa', 'Inactiva'])->default('Activa');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('categorias_asesoria');
    }
};
