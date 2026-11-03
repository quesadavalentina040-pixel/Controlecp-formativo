<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_acompaniamientos', function (Blueprint $table) {
            $table->id('id_acompanamiento');
            $table->foreignId('id_instructor')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_aprendiz')->constrained('users')->cascadeOnDelete();
            $table->string('categoria', 100);
            $table->text('motivo');
            $table->date('fecha_inicio');
            $table->unsignedSmallInteger('intervalo_dias');
            $table->unsignedSmallInteger('total_sesiones');
            $table->enum('estado', ['Activo', 'Finalizado'])->default('Activo');
            $table->text('resumen_final')->nullable();
            $table->string('objetivo_cumplido', 20)->nullable();
            $table->timestamps();
            $table->index(['id_instructor', 'estado']);
        });

        Schema::create('controlecp_acompanamiento_sesiones', function (Blueprint $table) {
            $table->id('id_sesion');
            $table->foreignId('id_acompanamiento')->constrained('controlecp_acompaniamientos', 'id_acompanamiento')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->default('08:00');
            $table->time('hora_fin')->default('09:00');
            $table->enum('estado', ['Programada', 'Realizada', 'No asistió', 'Cancelada'])->default('Programada');
            $table->text('observaciones')->nullable();
            $table->text('avances')->nullable();
            $table->text('compromisos')->nullable();
            $table->text('motivo')->nullable();
            $table->dateTime('registrada_en')->nullable();
            $table->timestamps();
            $table->index(['fecha', 'hora_inicio', 'hora_fin'], 'acomp_sesion_horario_idx');
        });

        Schema::create('controlecp_acompanamiento_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_acompanamiento')->constrained('controlecp_acompaniamientos', 'id_acompanamiento')->cascadeOnDelete();
            $table->foreignId('id_sesion')->nullable()->constrained('controlecp_acompanamiento_sesiones', 'id_sesion')->nullOnDelete();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->string('accion', 40);
            $table->text('motivo')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_acompanamiento_historial');
        Schema::dropIfExists('controlecp_acompanamiento_sesiones');
        Schema::dropIfExists('controlecp_acompaniamientos');
    }
};
