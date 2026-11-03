<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('controlecp_momentos', 'encuesta_id')) {
            Schema::table('controlecp_momentos', function (Blueprint $table) {
                $table->unsignedBigInteger('encuesta_id')->nullable()->after('estado');
                $table->foreign('encuesta_id', 'fk_momentos_encuesta')
                    ->references('id')
                    ->on('controlecp_encuestas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('controlecp_momentos', 'encuesta_id')) {
            Schema::table('controlecp_momentos', function (Blueprint $table) {
                $table->dropForeign('fk_momentos_encuesta');
                $table->dropColumn('encuesta_id');
            });
        }
    }
};
