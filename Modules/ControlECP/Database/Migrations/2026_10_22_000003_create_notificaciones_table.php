<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sistema de notificaciones transversales / Control ECP (HU-006 Criterio 12).
     */
    public function up(): void
    {
        if (!Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->id('id_notificacion');
                $table->unsignedBigInteger('id_usuario_receptor')->comment('Usuario que recibe la notificación');
                $table->unsignedBigInteger('id_usuario_causante')->nullable()->comment('Usuario que generó el evento');
                $table->string('modulo', 80)->default('ControlECP')->comment('Módulo de origen');
                $table->string('titulo', 200)->comment('Título corto o asunto');
                $table->text('mensaje')->comment('Cuerpo o detalle de la notificación');
                $table->string('referencia_tipo', 80)->nullable()->comment('Tipo de entidad: cronograma, inventario, pqr, etc.');
                $table->unsignedBigInteger('referencia_id')->nullable()->comment('ID del registro referenciado');
                $table->string('clave_evento', 80)->nullable()->comment('programar, editar, reasignar, posponer, alerta_cercania');
                $table->string('clave_agrupacion', 80)->nullable()->comment('Para agrupar notificaciones del mismo tipo');
                $table->integer('cantidad')->default(1)->comment('Contador si es agrupada');
                $table->boolean('leida')->default(false)->comment('0: no leída, 1: leída');
                $table->timestamp('fecha_generacion')->useCurrent()->comment('Fecha de creación');
                $table->timestamp('fecha_lectura')->nullable()->comment('Fecha cuando fue marcada como leída');
                $table->timestamps();

                $table->foreign('id_usuario_receptor')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_usuario_causante')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->index(['id_usuario_receptor', 'leida']);
                $table->index(['referencia_tipo', 'referencia_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
