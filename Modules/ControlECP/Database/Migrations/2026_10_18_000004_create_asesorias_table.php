<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para registrar solicitudes y programación de asesorías.
return new class extends Migration
{
    /**
     * Tabla asesorias
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de asesorias.
        Schema::create('asesorias', function (Blueprint $table) {
            // Identifica la categoría, aprendiz, instructor y usuarios que registran o cancelan.
            $table->id('id_asesoria');
            $table->unsignedBigInteger('id_categoria_asesoria');
            $table->unsignedBigInteger('id_usuario_aprendiz');
            $table->unsignedBigInteger('id_usuario_instructor')->nullable();
            $table->unsignedBigInteger('id_usuario_registro');
            $table->unsignedBigInteger('id_usuario_cancelador')->nullable();
            $table->text('motivo');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['Solicitada', 'Programada', 'Realizada', 'Cancelada'])->default('Solicitada');
            $table->text('motivo_cancelacion')->nullable();
            $table->dateTime('fecha_solicitud');

            // Vincula la asesoría con sus entidades; instructor y cancelador pueden quedar sin asignar.
            $table->foreign('id_categoria_asesoria')->references('id_categoria_asesoria')->on('categorias_asesoria')->onDelete('cascade');
            $table->foreign('id_usuario_aprendiz')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_usuario_instructor')->references('id')->on('users')->onDelete('set null');
            $table->foreign('id_usuario_registro')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_usuario_cancelador')->references('id')->on('users')->onDelete('set null');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('asesorias');
    }
};
