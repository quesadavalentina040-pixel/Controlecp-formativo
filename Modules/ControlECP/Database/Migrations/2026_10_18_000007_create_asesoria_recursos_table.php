<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para asociar recursos de inventario con asesorías.
return new class extends Migration
{
    /**
     * Tabla asesoria_recursos
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de asesoria_recursos.
        Schema::create('asesoria_recursos', function (Blueprint $table) {
            // Registra el recurso de inventario y la cantidad asignada a la asesoría.
            $table->id('id_asesoria_recurso');
            $table->unsignedBigInteger('id_asesoria');
            $table->unsignedBigInteger('id_inventario');
            $table->integer('cantidad')->default(1);

            // Mantiene las referencias y evita asignar dos veces el mismo recurso a una asesoría.
            $table->foreign('id_asesoria')->references('id_asesoria')->on('asesorias')->onDelete('cascade');
            $table->foreign('id_inventario')->references('id')->on('inventories')->onDelete('cascade');
            $table->unique(['id_asesoria', 'id_inventario']);
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('asesoria_recursos');
    }
};
