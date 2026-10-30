<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para registrar las franjas disponibles de cada instructor.
return new class extends Migration
{
    /**
     * Tabla disponibilidades_instructor
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de disponibilidades_instructor.
        Schema::create('disponibilidades_instructor', function (Blueprint $table) {
            // Identifica al instructor y el periodo, día y franja horaria disponible.
            $table->id('id_disponibilidad_instructor');
            $table->unsignedBigInteger('id_usuario');
            $table->integer('anio');
            $table->integer('trimestre');
            $table->integer('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('habilitada')->default(true);
            $table->boolean('vigente')->default(true);
            $table->dateTime('fecha_registro');

            // Elimina las disponibilidades cuando se elimina el usuario asociado.
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('disponibilidades_instructor');
    }
};
