<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'estado' a la tabla users para activar/desactivar usuarios
     * sin eliminar su información ni historial (HU7, HU8).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
