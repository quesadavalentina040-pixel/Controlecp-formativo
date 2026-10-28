<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear las notificaciones de Control ECP.
return new class extends Migration
{
    // Crea la tabla notifications para Control ECP
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de notifications.
        Schema::create('notifications', function (Blueprint $table) {
            // Registra destinatario, causante, contenido, evento, agrupación y estado de lectura.
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_usuario_receptor');
            $table->unsignedBigInteger('id_usuario_causante')->nullable();
            $table->string('modulo', 80);
            $table->string('titulo', 180);
            $table->text('mensaje')->nullable();
            $table->string('referencia_tipo', 100)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('clave_evento', 150);
            $table->string('clave_agrupacion', 150)->nullable();
            $table->integer('cantidad')->default(1);
            $table->boolean('leida')->default(false);
            $table->dateTime('fecha_generacion');
            $table->dateTime('fecha_lectura')->nullable();

            // El destinatario se elimina con su cuenta y el causante puede quedar sin referencia.
            $table->foreign('id_usuario_receptor')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_usuario_causante')->references('id')->on('users')->onDelete('set null');

            // Evita duplicar el evento por destinatario y agiliza consultas por módulo y fecha.
            $table->unique(['id_usuario_receptor', 'clave_evento'], 'notif_receptor_evento_unique');
            $table->index(['id_usuario_receptor', 'modulo', 'fecha_generacion'], 'notif_receptor_modulo_fecha_idx');
        });
    }

    // Elimina la tabla notifications
    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
