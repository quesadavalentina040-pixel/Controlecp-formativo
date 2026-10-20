<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla principal de PQRS para Control ECP (normalizada y prefijada como controlecp_pqrs).
     */
    public function up(): void
    {
        // Reemplazar la estructura anterior con el nuevo modelo relacional normalizado
        Schema::dropIfExists('controlecp_pqrs');

        Schema::create('controlecp_pqrs', function (Blueprint $table) {
            $table->id('id_pqrs');
            $table->string('radicado')->unique();
            $table->unsignedBigInteger('id_usuario_aprendiz');
            $table->unsignedBigInteger('id_tipo_pqrs');
            $table->unsignedBigInteger('id_usuario_instructor_destino')->nullable();
            $table->string('asunto');
            $table->text('descripcion');
            $table->string('estado')->default('Pendiente'); // Pendiente, En trámite, Resuelto, Cerrado
            $table->dateTime('fecha_radicacion');

            $table->softDeletes();
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('id_usuario_aprendiz')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('id_tipo_pqrs')
                ->references('id_tipo_pqrs')
                ->on('tipos_pqrs')
                ->onDelete('cascade');

            $table->foreign('id_usuario_instructor_destino')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_pqrs');
    }
};
