<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla cronograma para programar actividades de la Escuela Cultura de Paz (Control ECP - HU-006).
     */
    public function up(): void
    {
        if (!Schema::hasTable('cronograma')) {
            Schema::create('cronograma', function (Blueprint $table) {
                $table->id('id_cronograma');
                $table->unsignedBigInteger('id_actividad')->comment('FK hacia controlecp_actividades');
                $table->unsignedBigInteger('id_ficha')->comment('FK hacia courses (fichas)');
                $table->unsignedBigInteger('id_instructor')->comment('FK hacia users (instructor responsable)');
                $table->unsignedBigInteger('id_usuario_creador')->comment('FK hacia users (quien programó)');
                $table->unsignedBigInteger('id_usuario_finalizador')->nullable()->comment('FK hacia users (quien finalizó)');
                $table->date('fecha')->comment('Fecha programada de ejecución');
                $table->time('hora_inicio')->comment('Hora de inicio programada');
                $table->time('hora_fin')->comment('Hora de finalización programada');
                $table->string('lugar', 200)->comment('Lugar, aula o ambiente donde se realiza');
                $table->enum('estado', ['Pendiente', 'En proceso', 'Finalizada', 'Pospuesta', 'Cancelada'])
                      ->default('Pendiente')
                      ->comment('Estado de la sesión programada');
                $table->date('fecha_original')->nullable()->comment('Fecha inicial antes de posponer');
                $table->text('motivo_posposicion')->nullable()->comment('Justificación en caso de posponer');
                $table->timestamp('fecha_finalizacion')->nullable()->comment('Fecha y hora real de finalización');
                $table->timestamps();

                // Llaves foráneas
                $table->foreign('id_actividad')
                    ->references('id')
                    ->on('controlecp_actividades')
                    ->onDelete('cascade');

                $table->foreign('id_ficha')
                    ->references('id')
                    ->on('courses')
                    ->onDelete('cascade');

                $table->foreign('id_instructor')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_usuario_creador')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_usuario_finalizador')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                // Índices para optimizar búsquedas y semáforo
                $table->index(['fecha', 'estado']);
                $table->index(['id_instructor', 'fecha']);
                $table->index('id_ficha');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma');
    }
};
