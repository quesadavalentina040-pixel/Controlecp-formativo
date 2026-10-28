<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear el inventario de materiales.
return new class extends Migration
{
    /**
     * Tabla de inventario de materiales escolares para Control ECP.
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de materiales.
        Schema::create('materiales', function (Blueprint $table) {
            // Identificador y código irrepetible del material.
            $table->id();
            $table->string('codigo')->unique();
            // Descripción, clasificación, existencia y ubicación del inventario.
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', [
                'Papelería',
                'Escritura',
                'Arte y Manualidades',
                'Tecnología',
                'Mobiliario',
                'Aseo',
                'Otros',
            ])->default('Papelería');
            $table->unsignedInteger('cantidad')->default(0);
            $table->string('unidad')->default('Unidad'); // Unidad, Caja, Paquete, Resma...
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->string('ubicacion')->nullable(); // Bodega / Estante
            $table->enum('estado', ['Disponible', 'Agotado', 'Dañado'])->default('Disponible');

            // Conserva borrados lógicos y fechas de creación y actualización.
            $table->softDeletes();
            $table->timestamps();
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
