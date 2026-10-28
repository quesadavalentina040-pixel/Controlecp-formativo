<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migración para agregar la ruta de imagen a los materiales y retirarla al revertir.
return new class extends Migration
{
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // Ajusta el esquema de materiales.
        Schema::table('materiales', function (Blueprint $table) {
            // Guarda opcionalmente la ruta de la imagen del material.
            $table->string('imagen')->nullable()->after('descripcion');
        });
    }

    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        // Ajusta el esquema de materiales.
        Schema::table('materiales', function (Blueprint $table) {
            // Retira la columna agregada al aplicar esta migración.
            $table->dropColumn('imagen');
        });
    }
};
