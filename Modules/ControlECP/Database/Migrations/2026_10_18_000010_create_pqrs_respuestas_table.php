<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de respuestas a PQRS para Control ECP (controlecp_pqrs_respuestas).
     */
    public function up(): void
    {
        Schema::create('controlecp_pqrs_respuestas', function (Blueprint $table) {
            $table->id('id_pqrs_respuesta');
            $table->unsignedBigInteger('id_pqrs');
            $table->unsignedBigInteger('id_usuario'); // Usuario que emite la respuesta
            $table->text('respuesta');
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
        Schema::dropIfExists('controlecp_pqrs_respuestas');
    }
};
