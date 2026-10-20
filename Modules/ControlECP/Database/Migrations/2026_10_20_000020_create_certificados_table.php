<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de certificaciones ECP por ficha y momento.
 *
 * Soporta los 13 casos de uso del módulo de certificados:
 * - Un registro por combinación (course_id + momento_id) = un momento certificado
 * - Los 3 momentos de una ficha = ficha completamente certificada
 * - Integración preparada para API REST Central ERP SENA Empresa / SICEFA
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();

            // Ficha formativa certificada
            $table->unsignedBigInteger('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            // Momento certificado (1 = Autoconciencia, 2 = Convivencia, 3 = Resolución)
            $table->unsignedTinyInteger('momento_id')->comment('1, 2 o 3');

            // Responsable que certificó (administrador)
            $table->unsignedBigInteger('certificado_por');
            $table->foreign('certificado_por')->references('id')->on('users')->onDelete('restrict');

            // Estado del certificado
            $table->enum('estado', ['CERTIFICADA', 'ANULADA'])->default('CERTIFICADA');

            // Datos de la certificación
            $table->date('fecha_certificacion');
            $table->text('observacion')->nullable();

            // Actividades completadas en el momento al momento de certificar (JSON snapshot)
            $table->json('actividades_completadas')->nullable()
                ->comment('Snapshot de actividades con asistencia al momento de certificar');

            // Porcentaje de asistencia al momento de certificar
            $table->decimal('porcentaje_asistencia', 5, 2)->default(0);

            // Total aprendices en la ficha al certificar
            $table->unsignedSmallInteger('total_aprendices')->default(0);

            // Historial de cambios para auditoría (HU-012)
            $table->json('historial_cambios')->nullable();

            // Notificación generada (HU-011)
            $table->boolean('notificacion_generada')->default(false);
            $table->timestamp('notificacion_at')->nullable();

            // Integración con ERP SENA Empresa / SICEFA
            // HU-010: no emite a SOFIA Plus, solo registra localmente
            $table->boolean('emitido_sofia')->default(false)
                ->comment('HU-010: false siempre hasta que se conecte con SOFIA Plus');
            $table->unsignedBigInteger('sicefa_id')->nullable()
                ->comment('ID del registro en la API REST Central SICEFA');
            $table->timestamp('sicefa_synced_at')->nullable();
            $table->string('sicefa_token_verificacion', 64)->nullable()
                ->comment('Token único para verificación QR en SICEFA');

            // Anulación
            $table->text('motivo_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->timestamp('anulado_at')->nullable();

            $table->timestamps();

            // Una ficha solo puede tener UN certificado activo por momento (HU-007: evitar duplicados)
            $table->unique(['course_id', 'momento_id'], 'uq_certificado_ficha_momento');

            // Índices para búsquedas frecuentes
            $table->index('estado');
            $table->index('momento_id');
            $table->index('fecha_certificacion');
            $table->index('certificado_por');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('certificados');
    }
};
