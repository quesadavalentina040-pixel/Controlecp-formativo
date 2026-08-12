<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('politicas')) {
            Schema::create('politicas', function (Blueprint $table) {
                $table->id();
                $table->string('codigo')->unique(); // Ej: POL-2026-01
                $table->string('titulo');
                $table->string('tipo'); // Estratégica, Calidad, Seguridad, Operativa
                $table->text('descripcion');
                $table->integer('vigencia')->default(2026);
                $table->enum('estado', ['Activa', 'En Revisión', 'Inactiva'])->default('Activa');
                $table->string('responsable')->default('Dirección General');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('politicas');
    }
};
