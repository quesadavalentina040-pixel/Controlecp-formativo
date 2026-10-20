<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de Inventario de recursos y materiales para Control ECP.
     */
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->unsignedBigInteger('id_categoria_inventario');
            $table->unsignedBigInteger('id_usuario')->nullable(); // Usuario responsable / creador
            $table->string('nombre');
            $table->integer('cantidad_total')->default(0);
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('umbral_critico')->default(0);
            $table->integer('umbral_regular')->default(0);
            $table->string('estado_fisico')->nullable(); // Ej: Excelente, Bueno, Regular, Malo
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('Disponible'); // Disponible, Agotado, En Mantenimiento, De Baja
            $table->text('motivo_baja')->nullable();
            $table->dateTime('fecha_baja')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('id_categoria_inventario')
                ->references('id_categoria_inventario')
                ->on('categorias_inventario')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
