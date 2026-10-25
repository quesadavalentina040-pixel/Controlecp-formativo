<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de Actividades Pedagógicas de la Escuela Cultura de Paz.
     * Cada actividad pertenece a un Momento.
     */
    public function up(): void
    {
        Schema::create('controlecp_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('momento_id')
                  ->constrained('controlecp_momentos')
                  ->onDelete('cascade');
            $table->string('numero', 10)->default('01')
                  ->comment('Número de orden dentro del momento (01, 02, ...)');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();
            $table->string('duracion', 30)->default('2 Horas')
                  ->comment('Duración descriptiva, ej: 2 Horas, 1.5 Horas');
            $table->unsignedSmallInteger('duracion_min')->default(120)
                  ->comment('Duración en minutos para cálculos');
            $table->text('metodologia')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_actividades');
    }
};
