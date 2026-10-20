<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla pivote `apprentice_asistencia` que relaciona
 * aprendices con sesiones de asistencia de SENA Empresa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apprentice_asistencia', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('apprentice_id');
            $table->unsignedBigInteger('asistencia_id');

            // Estado de asistencia del aprendiz en la sesión
            $table->enum('asistencia', ['ASISTIO', 'FALTO', 'EXCUSA', 'RETARDO'])->default('ASISTIO');
            $table->text('observacion')->nullable()->comment('Observación sobre la asistencia del aprendiz');

            // Sincronización con SICEFA ERP
            $table->unsignedBigInteger('sicefa_id')->nullable()->comment('ID del registro en la API de SICEFA');
            $table->timestamp('sicefa_synced_at')->nullable();

            $table->timestamps();

            // Llaves foráneas
            $table->foreign('apprentice_id')
                ->references('id')
                ->on('apprentices')
                ->onDelete('cascade');

            $table->foreign('asistencia_id')
                ->references('id')
                ->on('asistencias')
                ->onDelete('cascade');

            // Un aprendiz solo puede tener un registro por sesión
            $table->unique(['apprentice_id', 'asistencia_id']);

            $table->index('asistencia');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('apprentice_asistencia');
    }
};
