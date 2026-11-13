<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_certificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('momento_id')->constrained('controlecp_momentos')->cascadeOnDelete();
            $table->foreignId('responsable_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('fecha_certificacion');
            $table->enum('estado', ['Certificada'])->default('Certificada');
            $table->timestamps();
            $table->unique(['course_id', 'momento_id']);
            $table->index(['estado', 'fecha_certificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_certificaciones');
    }
};
