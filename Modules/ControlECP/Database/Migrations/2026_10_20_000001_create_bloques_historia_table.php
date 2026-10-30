<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para crear los bloques de contenido de la historia.
return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla para almacenar los bloques de la historia de la Escuela Cultura de Paz (HU-008).
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Define las columnas y restricciones de bloques_historia.
        Schema::create('bloques_historia', function (Blueprint $table) {
            // Guarda contenido multimedia, su orden de presentación y si está visible.
            $table->id();
            $table->string('titulo');
            $table->text('texto');
            $table->enum('tipo_contenido', ['imagen', 'audio', 'video']);
            $table->string('ruta_archivo')->nullable();
            $table->text('url_video')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        Schema::dropIfExists('bloques_historia');
    }
};
