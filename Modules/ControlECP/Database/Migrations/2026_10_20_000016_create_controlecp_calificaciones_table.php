<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Calificaciones (estrellas + comentario) por actividad y aprendiz (HU18).
     */
    public function up(): void
    {
        Schema::create('controlecp_calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')
                  ->constrained('controlecp_actividades')
                  ->onDelete('cascade');
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Aprendiz que calificó');
            $table->unsignedTinyInteger('estrellas')
                  ->default(5)
                  ->comment('Calificación de 1 a 5 estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['actividad_id', 'course_id', 'user_id'],
                           'uq_calificacion_actividad_ficha_aprendiz');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_calificaciones');
    }
};
