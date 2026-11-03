<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_certificaciones_momento', function (Blueprint $table) {
            $table->id('id_certificacion');
            $table->unsignedBigInteger('id_ficha');
            $table->unsignedBigInteger('id_momento');
            $table->unsignedBigInteger('id_instructor');
            $table->dateTime('fecha_certificacion');
            $table->string('estado', 30)->default('Certificada');
            $table->timestamps();

            $table->unique(['id_ficha', 'id_momento'], 'uk_certificacion_ficha_momento');
            $table->foreign('id_ficha')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('id_momento')->references('id')->on('controlecp_momentos')->onDelete('cascade');
            $table->foreign('id_instructor')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_certificaciones_momento');
    }
};
