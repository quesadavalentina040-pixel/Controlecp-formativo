<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_calificaciones_asesorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesoria')
                ->constrained('asesorias', 'id_asesoria')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['id_asesoria', 'user_id'], 'uq_calificacion_asesoria_aprendiz');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_calificaciones_asesorias');
    }
};
