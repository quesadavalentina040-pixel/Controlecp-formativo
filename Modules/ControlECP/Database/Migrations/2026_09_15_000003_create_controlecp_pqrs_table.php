<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear el registro de peticiones, quejas, reclamos y sugerencias.
return new class extends Migration
{
    /**
     * Tabla de PQR (Peticiones, Quejas, Reclamos y Sugerencias) para Control ECP.
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de controlecp_pqrs.
        Schema::create('controlecp_pqrs', function (Blueprint $table) {
            // Identificador y radicado único para rastrear cada solicitud.
            $table->id();
            $table->string('radicado')->unique();

            // Tipo de solicitud
            $table->enum('tipo', [
                'Petición',
                'Queja',
                'Reclamo',
                'Sugerencia',
                'Felicitación',
            ])->default('Petición');

            // Datos del solicitante
            $table->string('nombre_solicitante');
            $table->string('documento')->nullable();
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->enum('perfil', ['Aprendiz', 'Instructor', 'Funcionario', 'Externo'])->default('Aprendiz');

            // Contenido
            $table->string('asunto');
            $table->text('mensaje');
            $table->string('anexo')->nullable();

            // Gestión
            $table->enum('prioridad', ['Baja', 'Media', 'Alta'])->default('Media');
            $table->enum('estado', ['Pendiente', 'En trámite', 'Resuelto', 'Cerrado'])->default('Pendiente');

            // Respuesta
            $table->text('respuesta')->nullable();
            $table->timestamp('fecha_respuesta')->nullable();
            // Vincula al responsable de respuesta; conserva el registro si el usuario se elimina.
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();

            // Permite borrado lógico y registra fechas de creación y actualización.
            $table->softDeletes();
            $table->timestamps();
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('controlecp_pqrs');
    }
};
