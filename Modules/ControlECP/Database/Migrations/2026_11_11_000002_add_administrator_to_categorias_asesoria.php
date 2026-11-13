<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_asesoria', function (Blueprint $table) {
            $table->foreignId('id_administrador')
                ->nullable()
                ->after('id_categoria_asesoria')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categorias_asesoria', function (Blueprint $table) {
            $table->dropForeign(['id_administrador']);
            $table->dropColumn('id_administrador');
        });
    }
};
