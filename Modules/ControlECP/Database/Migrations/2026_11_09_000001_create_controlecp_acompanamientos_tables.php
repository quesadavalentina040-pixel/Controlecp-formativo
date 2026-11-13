<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_acompanamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aprendiz_id')->constrained('users')->cascadeOnDelete();
            $table->string('categoria', 100);
            $table->text('motivo');
            $table->date('fecha_inicio');
            $table->unsignedSmallInteger('intervalo_dias');
            $table->unsignedSmallInteger('sesiones_planeadas');
            $table->string('estado', 20)->default('Activo');
            $table->text('resumen_final')->nullable();
            $table->boolean('objetivo_cumplido')->nullable();
            $table->timestamp('cerrado_en')->nullable();
            $table->timestamps();
            $table->index(['instructor_id', 'estado', 'aprendiz_id'], 'ecp_acomp_instructor_estado_aprendiz_idx');
        });

        Schema::create('controlecp_acompanamiento_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acompanamiento_id')->constrained('controlecp_acompanamientos')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado', 20)->default('Programada');
            $table->text('observaciones')->nullable();
            $table->text('avances')->nullable();
            $table->text('compromisos')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamp('registrada_en')->nullable();
            $table->timestamp('aviso_un_dia_enviado_en')->nullable();
            $table->timestamp('aviso_sin_registro_enviado_en')->nullable();
            $table->timestamps();
            $table->unique(['acompanamiento_id', 'numero'], 'ecp_acomp_sesion_numero_unique');
            $table->index(['fecha', 'hora_inicio', 'hora_fin', 'estado'], 'ecp_acomp_sesion_agenda_idx');
        });

        Schema::create('controlecp_acompanamiento_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acompanamiento_id')->constrained('controlecp_acompanamientos')->cascadeOnDelete();
            $table->foreignId('sesion_id')->nullable()->constrained('controlecp_acompanamiento_sesiones')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->string('accion', 40);
            $table->text('motivo')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
            $table->index(['acompanamiento_id', 'created_at'], 'ecp_acomp_historial_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_acompanamiento_historial');
        Schema::dropIfExists('controlecp_acompanamiento_sesiones');
        Schema::dropIfExists('controlecp_acompanamientos');
    }
};
