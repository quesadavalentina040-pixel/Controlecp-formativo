<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos de componentes (asistencia, encuesta, evidencia) y estado extendido
     * a la tabla controlecp_actividades para soportar las HU1–HU14.
     */
    public function up(): void
    {
        Schema::table('controlecp_actividades', function (Blueprint $table) {
            // Estado extendido: Activo / Inactivo / Finalizado
            $table->enum('estado', ['Activo', 'Inactivo', 'Finalizado'])
                  ->default('Activo')
                  ->change();

            // Componente Asistencia (HU6)
            $table->boolean('tiene_asistencia')->default(true)->after('estado')
                  ->comment('Indica si la actividad lleva registro de asistencia');

            // Componente Encuesta (HU7/HU8): ID de la encuesta del repositorio
            $table->unsignedBigInteger('encuesta_id')->nullable()->after('tiene_asistencia')
                  ->comment('FK hacia controlecp_encuestas');

            // Componente Evidencia (HU9)
            $table->boolean('tiene_evidencia')->default(true)->after('encuesta_id')
                  ->comment('Indica si la actividad acepta registro de evidencias');
        });
    }

    public function down(): void
    {
        Schema::table('controlecp_actividades', function (Blueprint $table) {
            $table->dropColumn(['tiene_asistencia', 'encuesta_id', 'tiene_evidencia']);
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo')->change();
        });
    }
};
