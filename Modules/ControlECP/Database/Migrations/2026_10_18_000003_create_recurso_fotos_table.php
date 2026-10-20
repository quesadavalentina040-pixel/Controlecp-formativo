<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de Fotos de Recursos/Materiales de Inventario para Control ECP.
     */
    public function up(): void
    {
        Schema::create('recurso_fotos', function (Blueprint $table) {
            $table->id('id_recurso_foto');
            $table->unsignedBigInteger('id_inventario');
            $table->string('ruta');
            $table->timestamps();

            // Llave foránea
            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso_fotos');
    }
};
