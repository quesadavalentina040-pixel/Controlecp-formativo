<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de PQR (Peticiones, Quejas, Reclamos y Sugerencias) para Control ECP.
     */
    public function up(): void
    {
        Schema::create('controlecp_pqrs', function (Blueprint $table) {
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
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_pqrs');
    }
};
