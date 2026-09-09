<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('elementos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');

            // Borrado suave
            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('elementos');
    }
};