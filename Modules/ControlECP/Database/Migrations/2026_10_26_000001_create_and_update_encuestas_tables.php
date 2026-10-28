<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Migración de creación y actualización del esquema de encuestas.
return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea y actualiza las tablas del módulo Repositorio > Encuestas (HU-009).
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // 1. Tabla de encuestas (controlecp_encuestas)
        if (Schema::hasTable('controlecp_encuestas')) {
            // Completa columnas faltantes, recupera el nombre anterior y ajusta estados permitidos.
            // Ajusta el esquema de controlecp_encuestas.
            Schema::table('controlecp_encuestas', function (Blueprint $table) {
                if (!Schema::hasColumn('controlecp_encuestas', 'nombre')) {
                    $table->string('nombre', 255)->after('id')->nullable();
                }
            });

            // Si existía titulo, respaldar datos a nombre
            if (Schema::hasColumn('controlecp_encuestas', 'titulo') && Schema::hasColumn('controlecp_encuestas', 'nombre')) {
                DB::statement("UPDATE controlecp_encuestas SET nombre = titulo WHERE nombre IS NULL OR nombre = ''");
            }

            // Asegurar estados 'Borrador', 'Activa', 'Inactiva'
            DB::statement("ALTER TABLE controlecp_encuestas MODIFY estado ENUM('Borrador', 'Activa', 'Inactiva') NOT NULL DEFAULT 'Borrador'");
        } else {
            // Define las columnas y restricciones de controlecp_encuestas.
            Schema::create('controlecp_encuestas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 255);
                $table->text('descripcion')->nullable();
                $table->enum('estado', ['Borrador', 'Activa', 'Inactiva'])->default('Borrador');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Tabla preguntas_encuesta
        if (!Schema::hasTable('preguntas_encuesta')) {
            // Define preguntas, tipo de respuesta, escala opcional y posición en la encuesta.
            // Define las columnas y restricciones de preguntas_encuesta.
            Schema::create('preguntas_encuesta', function (Blueprint $table) {
                $table->id();
                $table->foreignId('encuesta_id')->constrained('controlecp_encuestas')->onDelete('cascade');
                $table->text('texto');
                $table->enum('tipo', ['opcion_multiple', 'escala', 'texto']);
                $table->integer('escala_min')->nullable()->default(1);
                $table->integer('escala_max')->nullable()->default(5);
                $table->string('escala_etiqueta_min')->nullable();
                $table->string('escala_etiqueta_max')->nullable();
                $table->integer('orden')->default(0);
                $table->timestamps();
            });
        }

        // 3. Tabla opciones_pregunta
        if (!Schema::hasTable('opciones_pregunta')) {
            // Guarda las opciones de respuesta y su orden para cada pregunta.
            // Define las columnas y restricciones de opciones_pregunta.
            Schema::create('opciones_pregunta', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pregunta_id')->constrained('preguntas_encuesta')->onDelete('cascade');
                $table->string('texto');
                $table->integer('orden')->default(0);
                $table->timestamps();
            });
        }

        // 4. Tabla respuestas_encuesta
        if (!Schema::hasTable('respuestas_encuesta')) {
            // Registra respuestas y sus contextos; los índices apoyan búsquedas por actividad, programación y usuario.
            // Define las columnas y restricciones de respuestas_encuesta.
            Schema::create('respuestas_encuesta', function (Blueprint $table) {
                $table->id();
                $table->foreignId('encuesta_id')->constrained('controlecp_encuestas')->onDelete('cascade');
                $table->foreignId('pregunta_id')->constrained('preguntas_encuesta')->onDelete('cascade');
                $table->unsignedBigInteger('actividad_id')->nullable();
                $table->unsignedBigInteger('programacion_id')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->text('respuesta');
                $table->timestamps();

                $table->index('actividad_id');
                $table->index('programacion_id');
                $table->index('usuario_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        // Elimina primero las respuestas dependientes y después las opciones y preguntas.
        Schema::dropIfExists('respuestas_encuesta');
        Schema::dropIfExists('opciones_pregunta');
        Schema::dropIfExists('preguntas_encuesta');
        // No se elimina controlecp_encuestas por contener configuración previa
    }
};
