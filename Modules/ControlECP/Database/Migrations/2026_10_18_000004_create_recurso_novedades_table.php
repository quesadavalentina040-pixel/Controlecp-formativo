<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de Novedades de Recursos de Inventario para Control ECP.
     */
    public function up(): void
    {
        Schema::create('recurso_novedades', function (Blueprint $table) {
            $table->id('id_recurso_novedad');
            $table->unsignedBigInteger('id_inventario');
            $table->unsignedBigInteger('id_usuario');
            $table->text('descripcion');
            $table->dateTime('fecha');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso_novedades');
    }
};
