<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('opciones_pregunta') || !Schema::hasTable('preguntas')) {
            throw new RuntimeException('Las tablas de preguntas y opciones deben existir antes de reparar su esquema.');
        }

        if (!Schema::hasColumn('opciones_pregunta', 'id_pregunta')) {
            Schema::table('opciones_pregunta', function (Blueprint $table) {
                $table->unsignedBigInteger('id_pregunta')->nullable();
            });
        }

        if (Schema::hasColumn('opciones_pregunta', 'pregunta_id')) {
            DB::table('opciones_pregunta as opciones')
                ->join('preguntas as preguntas', 'preguntas.id_pregunta', '=', 'opciones.pregunta_id')
                ->whereNull('opciones.id_pregunta')
                ->update(['opciones.id_pregunta' => DB::raw('preguntas.id_pregunta')]);

            Schema::table('opciones_pregunta', function (Blueprint $table) {
                $table->unsignedBigInteger('pregunta_id')->nullable()->change();
            });
        }

        $foreignKey = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['opciones_pregunta', 'id_pregunta', 'preguntas']
        );

        if (!$foreignKey) {
            Schema::table('opciones_pregunta', function (Blueprint $table) {
                $table->foreign('id_pregunta', 'fk_opciones_pregunta_id_pregunta')
                    ->references('id_pregunta')
                    ->on('preguntas')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('opciones_pregunta')) {
            return;
        }

        if (Schema::hasColumn('opciones_pregunta', 'pregunta_id')
            && DB::table('opciones_pregunta')->whereNull('pregunta_id')->exists()) {
            throw new RuntimeException('No se puede revertir el esquema: existen opciones vinculadas solo a preguntas nuevas.');
        }

        $foreignKey = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['opciones_pregunta', 'id_pregunta', 'preguntas']
        );

        if ($foreignKey) {
            Schema::table('opciones_pregunta', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey->CONSTRAINT_NAME);
            });
        }

        if (Schema::hasColumn('opciones_pregunta', 'id_pregunta')) {
            Schema::table('opciones_pregunta', function (Blueprint $table) {
                $table->dropColumn('id_pregunta');
            });
        }

        if (Schema::hasColumn('opciones_pregunta', 'pregunta_id')) {
            Schema::table('opciones_pregunta', function (Blueprint $table) {
                $table->unsignedBigInteger('pregunta_id')->nullable(false)->change();
            });
        }
    }
};
