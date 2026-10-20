<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Encuestas del repositorio ECP (HU7/HU8).
     * Las encuestas se crean en el repositorio y se asocian a actividades.
     */
    public function up(): void
    {
        Schema::create('controlecp_encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_encuestas');
    }
};
