<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evidencias por actividad y ficha (HU9, HU15, HU16, HU17).
     * Una evidencia puede ser una foto (ruta local) o un enlace de video.
     */
    public function up(): void
    {
        Schema::create('controlecp_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')
                  ->constrained('controlecp_actividades')
                  ->onDelete('cascade');
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade')
                  ->comment('Ficha a la que pertenece esta evidencia');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Usuario que registró la evidencia');
            $table->enum('tipo', ['foto', 'video'])
                  ->comment('Tipo de evidencia: foto (jpg/png) o video (enlace URL)');
            $table->string('archivo', 500)->nullable()
                  ->comment('Ruta relativa del archivo subido (tipo foto)');
            $table->string('enlace', 500)->nullable()
                  ->comment('URL del video (tipo video)');
            $table->text('descripcion')->nullable()
                  ->comment('Descripción opcional de la evidencia');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_evidencias');
    }
};
