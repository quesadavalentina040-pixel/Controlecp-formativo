<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla puente asesoria_recursos (relación N:M entre asesorias e inventario).
     * Nota: id_asesoria se define como nullable según requerimiento.
     */
    public function up(): void
    {
        Schema::create('asesoria_recursos', function (Blueprint $table) {
            $table->id('id_asesoria_recurso');
            $table->unsignedBigInteger('id_asesoria')->nullable(); // Nullable según requerimiento
            $table->unsignedBigInteger('id_inventario');
            $table->integer('cantidad')->default(1);
            $table->timestamps();

            // Llave foránea hacia inventario
            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesoria_recursos');
    }
};
