<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de catálogo de Tipos de PQRS para Control ECP.
     */
    public function up(): void
    {
        Schema::create('tipos_pqrs', function (Blueprint $table) {
            $table->id('id_tipo_pqrs');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_pqrs');
    }
};
