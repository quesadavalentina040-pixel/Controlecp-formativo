<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extiende asistencia_registros para soportar los 13 casos de uso del módulo:
 * - Evidencia (foto/video)           → evidencia_url, evidencia_tipo
 * - Anulación con motivo             → estado ANULADA, motivo_anulacion
 * - Edición con motivo               → motivo_edicion, editado_por, editado_at
 * - Auditoría de cambios             → historial JSON
 * - Fuente de datos (API/BD local)   → fuente
 * - Momento ECP                      → momento_id
 * - SICEFA sync                      → sicefa_id, sicefa_synced_at
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_registros', function (Blueprint $table) {
            // Estado extendido con ANULADA
            $table->enum('estado', ['ASISTIO', 'FALTO', 'EXCUSA', 'RETARDO', 'ANULADA'])
                  ->default('ASISTIO')
                  ->change();

            // Momento ECP del que hace parte la actividad (1, 2 o 3)
            $table->unsignedTinyInteger('momento_id')->default(1)->after('actividad_nombre');

            // Evidencia opcional (foto o enlace video)
            $table->string('evidencia_url', 500)->nullable()->after('observacion');
            $table->enum('evidencia_tipo', ['foto', 'video', 'enlace'])->nullable()->after('evidencia_url');

            // Auditoría de edición
            $table->text('motivo_edicion')->nullable()->after('evidencia_tipo');
            $table->unsignedBigInteger('editado_por')->nullable()->after('motivo_edicion');
            $table->timestamp('editado_at')->nullable()->after('editado_por');

            // Motivo de anulación
            $table->text('motivo_anulacion')->nullable()->after('editado_at');
            $table->unsignedBigInteger('anulado_por')->nullable()->after('motivo_anulacion');
            $table->timestamp('anulado_at')->nullable()->after('anulado_por');

            // Historial de cambios en JSON (para auditoría HU-010)
            $table->json('historial_cambios')->nullable()->after('anulado_at');

            // Fuente del dato (api_central o bd_local)
            $table->enum('fuente', ['api_central', 'bd_local'])->default('bd_local')->after('historial_cambios');

            // Sincronización con SICEFA ERP
            $table->unsignedBigInteger('sicefa_id')->nullable()->after('fuente');
            $table->timestamp('sicefa_synced_at')->nullable()->after('sicefa_id');

            // Índices adicionales
            $table->index('actividad_id');
            $table->index('instructor_id');
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_registros', function (Blueprint $table) {
            $table->dropColumn([
                'momento_id', 'evidencia_url', 'evidencia_tipo',
                'motivo_edicion', 'editado_por', 'editado_at',
                'motivo_anulacion', 'anulado_por', 'anulado_at',
                'historial_cambios', 'fuente', 'sicefa_id', 'sicefa_synced_at',
            ]);
        });
    }
};
