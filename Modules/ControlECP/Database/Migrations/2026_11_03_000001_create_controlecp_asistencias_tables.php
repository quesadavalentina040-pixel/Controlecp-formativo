<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronograma', 'id_cronograma')->cascadeOnDelete();
            $table->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
            $table->enum('estado', ['Registrada', 'Anulada'])->default('Registrada');
            $table->text('motivo_anulacion')->nullable();
            $table->timestamp('registrada_en')->useCurrent();
            $table->timestamps();
            $table->unique('cronograma_id');
            $table->index(['estado', 'registrada_en']);
        });

        Schema::create('controlecp_asistencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('controlecp_asistencias')->cascadeOnDelete();
            $table->foreignId('aprendiz_id')->constrained('apprentices')->cascadeOnDelete();
            $table->boolean('presente')->default(true);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->unique(['asistencia_id', 'aprendiz_id']);
        });

        Schema::create('controlecp_asistencia_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->constrained('controlecp_asistencias')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->enum('accion', ['Registrada', 'Editada', 'Anulada']);
            $table->text('motivo')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
            $table->index(['asistencia_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_asistencia_historial');
        Schema::dropIfExists('controlecp_asistencia_detalles');
        Schema::dropIfExists('controlecp_asistencias');
    }
};
