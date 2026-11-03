<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_asistencias', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->foreignId('id_cronograma')->constrained('cronograma', 'id_cronograma')->cascadeOnDelete();
            $table->foreignId('id_instructor')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_ficha')->constrained('courses')->cascadeOnDelete();
            $table->enum('estado', ['Registrada', 'Anulada'])->default('Registrada');
            $table->text('observacion')->nullable();
            $table->string('evidencia_tipo', 20)->nullable();
            $table->string('evidencia_archivo', 500)->nullable();
            $table->string('evidencia_enlace', 500)->nullable();
            $table->timestamps();
            $table->unique('id_cronograma');
        });

        Schema::create('controlecp_asistencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asistencia')->constrained('controlecp_asistencias', 'id_asistencia')->cascadeOnDelete();
            $table->foreignId('id_aprendiz')->constrained('apprentices')->cascadeOnDelete();
            $table->boolean('presente')->default(true);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->unique(['id_asistencia', 'id_aprendiz']);
        });

        Schema::create('controlecp_asistencia_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asistencia')->constrained('controlecp_asistencias', 'id_asistencia')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->string('accion', 30);
            $table->text('motivo');
            $table->json('datos_anteriores')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_asistencia_historial');
        Schema::dropIfExists('controlecp_asistencia_detalles');
        Schema::dropIfExists('controlecp_asistencias');
    }
};
