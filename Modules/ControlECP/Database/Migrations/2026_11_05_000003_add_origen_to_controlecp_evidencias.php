<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controlecp_evidencias', function (Blueprint $table) {
            $table->string('origen', 20)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('controlecp_evidencias', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
