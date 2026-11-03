<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controlecp_momentos', function (Blueprint $table) {
            $table->dropForeign('fk_momentos_encuesta');
            $table->foreign('encuesta_id', 'fk_momentos_encuesta')
                ->references('id_encuesta')
                ->on('encuestas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controlecp_momentos', function (Blueprint $table) {
            $table->dropForeign('fk_momentos_encuesta');
            $table->foreign('encuesta_id', 'fk_momentos_encuesta')
                ->references('id')
                ->on('controlecp_encuestas')
                ->nullOnDelete();
        });
    }
};
