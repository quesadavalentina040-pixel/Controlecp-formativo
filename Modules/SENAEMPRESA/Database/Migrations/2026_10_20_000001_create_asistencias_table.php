<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla `asistencias` que almacena las sesiones de asistencia
 * de aprendices en el marco del SENA Empresa / SICEFA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();

            // Datos de la sesión de asistencia
            $table->string('nombre', 200)->comment('Nombre o descripción de la sesión de asistencia');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();

            // Estado y tipo
            $table->enum('estado', ['ACTIVA', 'CERRADA', 'CANCELADA'])->default('ACTIVA');
            $table->enum('tipo', ['PRESENCIAL', 'VIRTUAL', 'MIXTA'])->default('PRESENCIAL');

            // Sincronización con SICEFA ERP
            $table->unsignedBigInteger('sicefa_id')->nullable()->comment('ID del registro en la API de SICEFA');
            $table->timestamp('sicefa_synced_at')->nullable()->comment('Fecha de última sincronización con SICEFA');

            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('fecha');
            $table->index('estado');
            $table->index('sicefa_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asistencias');
    }
};
