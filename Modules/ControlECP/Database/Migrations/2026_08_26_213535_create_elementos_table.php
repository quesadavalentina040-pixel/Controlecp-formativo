<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear el catálogo de elementos.
return new class extends Migration
{
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up()
    {
        // Define las columnas y restricciones de elementos.
        Schema::create('elementos', function (Blueprint $table) {
            // Identificador, código único y datos descriptivos del elemento.
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');

            // Borrado suave
            $table->softDeletes();

            // Fechas de creación y actualización automáticas.
            $table->timestamps();
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down()
    {
        Schema::dropIfExists('elementos');
    }
};