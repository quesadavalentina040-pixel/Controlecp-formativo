<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('nickname', [
                'admin_controlecp',
                'instru_controlecp',
                'apren_controlecp',
            ])
            ->whereIn('email', [
                'admin@controlecp.com',
                'instru@controlecp.com',
                'apren@controlecp.com',
            ])
            ->update(['origen_registro' => 'sistema']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('nickname', [
                'admin_controlecp',
                'instru_controlecp',
                'apren_controlecp',
            ])
            ->whereIn('email', [
                'admin@controlecp.com',
                'instru@controlecp.com',
                'apren@controlecp.com',
            ])
            ->update(['origen_registro' => 'importado']);
    }
};
