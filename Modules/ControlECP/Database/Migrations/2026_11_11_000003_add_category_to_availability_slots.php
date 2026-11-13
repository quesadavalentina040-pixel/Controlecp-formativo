<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disponibilidades_instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_categoria_asesoria')->nullable()->after('id_usuario');
            $table->foreign('id_categoria_asesoria')
                ->references('id_categoria_asesoria')
                ->on('categorias_asesoria')
                ->nullOnDelete();
        });

        $userIds = DB::table('disponibilidades_instructor')
            ->where('vigente', true)
            ->distinct()
            ->pluck('id_usuario');
        foreach ($userIds as $userId) {
            $categoryIds = DB::table('categorias_asesoria')
                ->where('id_administrador', $userId)
                ->where('estado', 'Activa')
                ->pluck('id_categoria_asesoria');
            if ($categoryIds->count() === 1) {
                DB::table('disponibilidades_instructor')
                    ->where('id_usuario', $userId)
                    ->where('vigente', true)
                    ->whereNull('id_categoria_asesoria')
                    ->update(['id_categoria_asesoria' => $categoryIds->first()]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('disponibilidades_instructor', function (Blueprint $table) {
            $table->dropForeign(['id_categoria_asesoria']);
            $table->dropColumn('id_categoria_asesoria');
        });
    }
};
