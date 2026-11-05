<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla principal de PQRS para Control ECP (normalizada y prefijada como controlecp_pqrs).
     */
    public function up(): void
    {
        Schema::table('controlecp_pqrs', function (Blueprint $table) {
            if (!Schema::hasColumn('controlecp_pqrs', 'id_usuario_aprendiz')) {
                $table->unsignedBigInteger('id_usuario_aprendiz')->nullable();
            }
            if (!Schema::hasColumn('controlecp_pqrs', 'id_tipo_pqrs')) {
                $table->unsignedBigInteger('id_tipo_pqrs')->nullable();
            }
            if (!Schema::hasColumn('controlecp_pqrs', 'id_usuario_instructor_destino')) {
                $table->unsignedBigInteger('id_usuario_instructor_destino')->nullable();
            }
            if (!Schema::hasColumn('controlecp_pqrs', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
            if (!Schema::hasColumn('controlecp_pqrs', 'fecha_radicacion')) {
                $table->dateTime('fecha_radicacion')->nullable();
            }
        });

        if (Schema::hasColumn('controlecp_pqrs', 'mensaje')) {
            DB::table('controlecp_pqrs')
                ->whereNull('descripcion')
                ->update(['descripcion' => DB::raw('mensaje')]);
        }

        if (Schema::hasColumn('controlecp_pqrs', 'created_at')) {
            DB::table('controlecp_pqrs')
                ->whereNull('fecha_radicacion')
                ->update(['fecha_radicacion' => DB::raw('created_at')]);
        }

        foreach (['Petición', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación'] as $nombreTipo) {
            DB::table('tipos_pqrs')->updateOrInsert(
                ['nombre' => $nombreTipo],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $idTipo = DB::table('tipos_pqrs')
                ->where('nombre', $nombreTipo)
                ->value('id_tipo_pqrs');

            if (Schema::hasColumn('controlecp_pqrs', 'tipo')) {
                DB::table('controlecp_pqrs')
                    ->where('tipo', $nombreTipo)
                    ->whereNull('id_tipo_pqrs')
                    ->update(['id_tipo_pqrs' => $idTipo]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('controlecp_pqrs', function (Blueprint $table) {
            foreach ([
                'fecha_radicacion',
                'descripcion',
                'id_usuario_instructor_destino',
                'id_tipo_pqrs',
                'id_usuario_aprendiz',
            ] as $column) {
                if (Schema::hasColumn('controlecp_pqrs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
