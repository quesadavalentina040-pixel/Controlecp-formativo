<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de historial y trazabilidad de cambios de estado de PQRS para Control ECP (controlecp_pqrs_historial).
     */
    public function up(): void
    {
        Schema::create('controlecp_pqrs_historial', function (Blueprint $table) {
            $table->id('id_pqrs_historial');
            $table->unsignedBigInteger('id_pqrs');
            $table->unsignedBigInteger('id_usuario'); // Usuario que realiza el cambio de estado
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('observacion')->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('id_pqrs')
                ->references('id_pqrs')
                ->on('controlecp_pqrs')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_pqrs_historial');
    }
};
