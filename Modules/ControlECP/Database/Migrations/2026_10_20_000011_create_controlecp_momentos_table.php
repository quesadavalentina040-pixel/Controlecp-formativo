<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de Momentos Pedagógicos de la Escuela Cultura de Paz.
     * Los 3 momentos base (Autoconciencia, Convivencia, Resolución) se cargan
     * vía seeder. El administrador puede crear, editar y activar/desactivar.
     */
    public function up(): void
    {
        Schema::create('controlecp_momentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('icono', 80)->default('fa-star')
                  ->comment('Clase FontAwesome para el ícono del momento');
            $table->string('color', 20)->default('#39A900')
                  ->comment('Color hexadecimal del momento');
            $table->unsignedTinyInteger('orden')->default(0)
                  ->comment('Orden de presentación en el listado');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_momentos');
    }
};
