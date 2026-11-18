<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'preguntas_encuesta';

    private const COLUMNS = [
        'escala_min',
        'escala_max',
        'escala_etiqueta_min',
        'escala_etiqueta_max',
    ];

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        $columns = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => Schema::hasColumn(self::TABLE, $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        $missingColumns = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => !Schema::hasColumn(self::TABLE, $column)
        ));

        if ($missingColumns === []) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($missingColumns) {
            if (in_array('escala_min', $missingColumns, true)) {
                $table->integer('escala_min')->nullable()->default(1);
            }
            if (in_array('escala_max', $missingColumns, true)) {
                $table->integer('escala_max')->nullable()->default(5);
            }
            if (in_array('escala_etiqueta_min', $missingColumns, true)) {
                $table->string('escala_etiqueta_min')->nullable();
            }
            if (in_array('escala_etiqueta_max', $missingColumns, true)) {
                $table->string('escala_etiqueta_max')->nullable();
            }
        });
    }
};
