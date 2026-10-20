<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_registros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->comment('Ficha formativa');
            $table->unsignedBigInteger('instructor_id')->comment('Usuario instructor que registró');
            $table->unsignedBigInteger('actividad_id')->comment('ID de la actividad ECP (1-18)');
            $table->string('actividad_nombre', 200);
            $table->unsignedBigInteger('apprentice_id')->nullable()->comment('ID local del aprendiz si existe');
            $table->string('document_number', 30)->comment('Número de documento del aprendiz');
            $table->string('full_name', 200)->comment('Nombre completo del aprendiz');
            $table->enum('estado', ['ASISTIO', 'FALTO', 'EXCUSA', 'RETARDO'])->default('ASISTIO');
            $table->text('observacion')->nullable();
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');

            // Un aprendiz solo puede tener un registro por actividad/fecha/ficha
            $table->unique(['course_id', 'actividad_id', 'fecha', 'document_number'], 'uq_asistencia_registro');

            $table->index('fecha');
            $table->index('course_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asistencia_registros');
    }
};
