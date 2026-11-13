<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controlecp_acompanamiento_sesiones', function (Blueprint $table) {
            $table->timestamp('aviso_aprendiz_enviado_en')->nullable()->after('aviso_un_dia_enviado_en');
        });
    }

    public function down(): void
    {
        Schema::table('controlecp_acompanamiento_sesiones', function (Blueprint $table) {
            $table->dropColumn('aviso_aprendiz_enviado_en');
        });
    }
};
