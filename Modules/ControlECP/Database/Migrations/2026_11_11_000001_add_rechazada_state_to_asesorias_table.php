<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesorias', function (Blueprint $table) {
            $table->enum('estado', ['Solicitada', 'Programada', 'Realizada', 'Cancelada', 'Rechazada'])->default('Solicitada')->change();
        });

        Schema::table('asesoria_historial', function (Blueprint $table) {
            $table->enum('accion', ['Reprogramo', 'Cancelo', 'Acepto', 'Rechazo'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('asesoria_historial', function (Blueprint $table) {
            $table->enum('accion', ['Reprogramo', 'Cancelo'])->change();
        });

        Schema::table('asesorias', function (Blueprint $table) {
            $table->enum('estado', ['Solicitada', 'Programada', 'Realizada', 'Cancelada'])->default('Solicitada')->change();
        });
    }
};
