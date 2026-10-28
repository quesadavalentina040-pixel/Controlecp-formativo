<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración de las tablas del módulo POE según la HU-011.
return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea las tablas del módulo POE de Control ECP según la HU-011.
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // 1. Tabla poes
        if (!Schema::hasTable('poes')) {
            // Guarda identificador, líder actual, código único y nombre del POE.
            // Define las columnas y restricciones de poes.
            Schema::create('poes', function (Blueprint $table) {
                $table->id('id_poe');
                $table->unsignedBigInteger('id_usuario')->comment('Líder actual');
                $table->string('codigo')->unique();
                $table->string('nombre');

                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 2. Tabla poe_programaciones (se crea antes de poe_versiones por la FK)
        if (!Schema::hasTable('poe_programaciones')) {
            // Registra líderes, fechas, estado, cancelación y motivo de cada programación.
            // Define las columnas y restricciones de poe_programaciones.
            Schema::create('poe_programaciones', function (Blueprint $table) {
                $table->id('id_poe_programacion');
                $table->unsignedBigInteger('id_poe');
                $table->unsignedBigInteger('id_usuario_lider_actual');
                $table->unsignedBigInteger('id_usuario_nuevo_lider');
                $table->date('fecha_limite');
                $table->date('fecha_original')->nullable();
                $table->string('estado')->default('Pendiente')->comment('Pendiente / Cumplida / Atrasada');
                $table->boolean('cancelada')->default(false);
                $table->text('motivo_cambio')->nullable();
                $table->dateTime('fecha_cumplimiento')->nullable();

                $table->foreign('id_poe')->references('id_poe')->on('poes')->onDelete('cascade');
                $table->foreign('id_usuario_lider_actual')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('id_usuario_nuevo_lider')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 3. Tabla poe_versiones
        if (!Schema::hasTable('poe_versiones')) {
            // Guarda archivo, número, cambios y autor de cada versión, con unicidad por POE y versión.
            // Define las columnas y restricciones de poe_versiones.
            Schema::create('poe_versiones', function (Blueprint $table) {
                $table->id('id_poe_version');
                $table->unsignedBigInteger('id_poe');
                $table->unsignedBigInteger('id_usuario')->comment('Quien subió la versión');
                $table->unsignedBigInteger('id_poe_programacion')->nullable();
                $table->string('numero_version');
                $table->text('cambio');
                $table->string('archivo');
                $table->string('formato')->comment('PDF / Word');
                $table->boolean('es_actual')->default(true);
                $table->dateTime('fecha');

                $table->unique(['id_poe', 'numero_version']);
                $table->foreign('id_poe')->references('id_poe')->on('poes')->onDelete('cascade');
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('id_poe_programacion')->references('id_poe_programacion')->on('poe_programaciones')->onDelete('set null');
            });
        }

        // 4. Tabla poe_lideres_historial
        if (!Schema::hasTable('poe_lideres_historial')) {
            // Conserva los periodos en que cada usuario lideró un POE.
            // Define las columnas y restricciones de poe_lideres_historial.
            Schema::create('poe_lideres_historial', function (Blueprint $table) {
                $table->id('id_poe_lider_historial');
                $table->unsignedBigInteger('id_poe');
                $table->unsignedBigInteger('id_usuario')->comment('Líder');
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();

                $table->foreign('id_poe')->references('id_poe')->on('poes')->onDelete('cascade');
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 5. Agregar id_poe_programacion a la tabla notifications de Control ECP (aditivo)
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'id_poe_programacion')) {
            // Relaciona opcionalmente la notificación con la programación correspondiente.
            // Ajusta el esquema de notifications.
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('id_poe_programacion')->nullable()->after('referencia_id');
                $table->foreign('id_poe_programacion')->references('id_poe_programacion')->on('poe_programaciones')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        // Retira primero la relación aditiva de notifications antes de eliminar las tablas POE.
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'id_poe_programacion')) {
            // Ajusta el esquema de notifications.
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['id_poe_programacion']);
                $table->dropColumn('id_poe_programacion');
            });
        }

        Schema::dropIfExists('poe_lideres_historial');
        Schema::dropIfExists('poe_versiones');
        Schema::dropIfExists('poe_programaciones');
        Schema::dropIfExists('poe_poes');
        Schema::dropIfExists('poes');
    }
};
