<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidad_categoria_usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->primary();
            $table->unsignedBigInteger('id_categoria_asesoria');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_categoria_asesoria')
                ->references('id_categoria_asesoria')
                ->on('categorias_asesoria')
                ->onDelete('cascade');
        });

        $usersWithSingleCategory = DB::table('disponibilidades_instructor')
            ->where('vigente', true)
            ->whereNotNull('id_categoria_asesoria')
            ->select('id_usuario')
            ->groupBy('id_usuario')
            ->havingRaw('COUNT(DISTINCT id_categoria_asesoria) = 1')
            ->pluck('id_usuario');

        foreach ($usersWithSingleCategory as $userId) {
            $categoryId = DB::table('disponibilidades_instructor')
                ->where('id_usuario', $userId)
                ->where('vigente', true)
                ->whereNotNull('id_categoria_asesoria')
                ->value('id_categoria_asesoria');

            DB::table('disponibilidad_categoria_usuario')->insert([
                'id_usuario' => $userId,
                'id_categoria_asesoria' => $categoryId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_categoria_usuario');
    }
};
