<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_calificaciones_asesoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesoria_id')
                ->unique()
                ->constrained('asesorias', 'id_asesoria')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_calificaciones_asesoria');
    }
};
