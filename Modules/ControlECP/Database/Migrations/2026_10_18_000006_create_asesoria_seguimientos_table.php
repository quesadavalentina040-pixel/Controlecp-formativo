<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para guardar seguimientos y sus correcciones de asesoría.
return new class extends Migration
{
    /**
     * Tabla asesoria_seguimientos
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de asesoria_seguimientos.
        Schema::create('asesoria_seguimientos', function (Blueprint $table) {
            // Guarda avances, compromisos, observaciones y datos de una posible corrección.
            $table->id('id_asesoria_seguimiento');
            $table->unsignedBigInteger('id_asesoria');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_asesoria_seguimiento_corrige')->nullable();
            $table->text('avances');
            $table->text('compromisos');
            $table->text('observaciones')->nullable();
            $table->text('motivo_correccion')->nullable();
            $table->dateTime('fecha');

            // Vincula asesoría, autor y, si aplica, el seguimiento original corregido.
            $table->foreign('id_asesoria')->references('id_asesoria')->on('asesorias')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_asesoria_seguimiento_corrige')->references('id_asesoria_seguimiento')->on('asesoria_seguimientos')->onDelete('set null');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('asesoria_seguimientos');
    }
};
