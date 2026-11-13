<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_acompanamiento_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('controlecp_acompanamiento_sesiones')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['foto', 'video']);
            $table->string('archivo', 500)->nullable();
            $table->string('enlace', 500)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'fecha_registro'], 'ecp_acomp_evidencia_usuario_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_acompanamiento_evidencias');
    }
};
