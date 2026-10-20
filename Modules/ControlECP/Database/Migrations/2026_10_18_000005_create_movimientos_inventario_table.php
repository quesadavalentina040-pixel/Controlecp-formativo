<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de Movimientos de Inventario para Control ECP.
     */
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento_inventario');
            $table->unsignedBigInteger('id_inventario');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_movimiento_inventario_origen')->nullable();
            $table->unsignedBigInteger('id_cronograma')->nullable(); // Opcional / Relación con cronograma
            $table->unsignedBigInteger('id_asesoria')->nullable(); // Opcional / Relación con asesorías

            $table->string('tipo'); // Entrada, Salida, Préstamo, Devolución, Baja, Ajuste
            $table->integer('cantidad');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo')->nullable();
            $table->text('observacion')->nullable();
            $table->string('foto')->nullable();
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

            // Autorrelación para trazabilidad (ej. devolución que proviene de un préstamo)
            $table->foreign('id_movimiento_inventario_origen')
                ->references('id_movimiento_inventario')
                ->on('movimientos_inventario')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
