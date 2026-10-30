<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para conservar el historial de cambios de las asesorías.
return new class extends Migration
{
    /**
     * Tabla asesoria_historial
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de asesoria_historial.
        Schema::create('asesoria_historial', function (Blueprint $table) {
            // Registra acción, fechas anteriores y nuevas, motivo y momento del cambio.
            $table->id('id_asesoria_historial');
            $table->unsignedBigInteger('id_asesoria');
            $table->unsignedBigInteger('id_usuario');
            $table->enum('accion', ['Reprogramo', 'Cancelo']);
            $table->date('fecha_anterior')->nullable();
            $table->time('hora_anterior')->nullable();
            $table->date('fecha_nueva')->nullable();
            $table->time('hora_nueva')->nullable();
            $table->text('motivo');
            $table->dateTime('fecha');

            // Relaciona cada cambio con la asesoría afectada y el usuario que lo registró.
            $table->foreign('id_asesoria')->references('id_asesoria')->on('asesorias')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('asesoria_historial');
    }
};
