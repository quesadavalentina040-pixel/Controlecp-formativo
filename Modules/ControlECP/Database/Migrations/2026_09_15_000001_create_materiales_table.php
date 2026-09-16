<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de inventario de materiales escolares para Control ECP.
     */
    public function up(): void
    {
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', [
                'Papelería',
                'Escritura',
                'Arte y Manualidades',
                'Tecnología',
                'Mobiliario',
                'Aseo',
                'Otros',
            ])->default('Papelería');
            $table->unsignedInteger('cantidad')->default(0);
            $table->string('unidad')->default('Unidad'); // Unidad, Caja, Paquete, Resma...
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->string('ubicacion')->nullable(); // Bodega / Estante
            $table->enum('estado', ['Disponible', 'Agotado', 'Dañado'])->default('Disponible');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
