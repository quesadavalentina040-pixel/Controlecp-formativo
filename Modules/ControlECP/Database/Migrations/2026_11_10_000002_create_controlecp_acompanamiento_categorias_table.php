<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlecp_acompanamiento_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->timestamps();
            $table->unique(['instructor_id', 'nombre'], 'ecp_acomp_categoria_instructor_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlecp_acompanamiento_categorias');
    }
};
