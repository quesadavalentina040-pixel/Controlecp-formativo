<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Historial de auditoría para cambios en el cronograma (Control ECP - HU-006).
     */
    public function up(): void
    {
        if (!Schema::hasTable('cronograma_historial')) {
            Schema::create('cronograma_historial', function (Blueprint $table) {
                $table->id('id_cronograma_historial');
                $table->unsignedBigInteger('id_cronograma')->comment('FK hacia cronograma');
                $table->unsignedBigInteger('id_usuario_autor')->comment('Usuario que ejecutó la acción');
                $table->unsignedBigInteger('id_usuario_instructor_anterior')->nullable()->comment('Instructor anterior si hubo reasignación');
                $table->unsignedBigInteger('id_usuario_instructor_nuevo')->nullable()->comment('Nuevo instructor si hubo reasignación');
                $table->string('accion', 50)->comment('crear, editar, reasignar, posponer, eliminar, finalizar');
                $table->date('fecha_anterior')->nullable()->comment('Fecha anterior al cambio o posposición');
                $table->date('fecha_nueva')->nullable()->comment('Nueva fecha programada');
                $table->text('motivo')->nullable()->comment('Motivo o justificación del cambio');
                $table->timestamp('fecha')->useCurrent()->comment('Fecha y hora del registro de historial');
                $table->timestamps();

                // Llaves foráneas
                $table->foreign('id_cronograma')
                    ->references('id_cronograma')
                    ->on('cronograma')
                    ->onDelete('cascade');

                $table->foreign('id_usuario_autor')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_usuario_instructor_anterior')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->foreign('id_usuario_instructor_nuevo')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->index('id_cronograma');
                $table->index('accion');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma_historial');
    }
};
