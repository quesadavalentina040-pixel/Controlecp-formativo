<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla puente cronograma_recursos (relación N:M entre cronograma e inventario).
     * Nota: id_cronograma se define como nullable para permitir operar antes de crear cronograma.
     */
    public function up(): void
    {
        Schema::create('cronograma_recursos', function (Blueprint $table) {
            $table->id('id_cronograma_recurso');
            $table->unsignedBigInteger('id_cronograma')->nullable(); // Nullable
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
        Schema::dropIfExists('cronograma_recursos');
    }
};
