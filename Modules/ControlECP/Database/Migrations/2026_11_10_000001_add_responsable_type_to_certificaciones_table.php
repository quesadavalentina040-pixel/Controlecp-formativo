<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('controlecp_certificaciones', 'responsable_tipo')) {
            Schema::table('controlecp_certificaciones', function (Blueprint $table) {
                $table->string('responsable_tipo', 20)->default('admin')->after('responsable_id');
            });
        }

        $indexes = Schema::getIndexes('controlecp_certificaciones');
        $hasCourseIndex = collect($indexes)->contains(
            fn ($index) => $index['columns'] === ['course_id']
        );
        if (!$hasCourseIndex) {
            Schema::table('controlecp_certificaciones', function (Blueprint $table) {
                $table->index('course_id');
            });
        }

        $indexes = Schema::getIndexes('controlecp_certificaciones');
        $hasOriginalUnique = collect($indexes)->contains(
            fn ($index) => $index['columns'] === ['course_id', 'momento_id'] && $index['unique']
        );
        if ($hasOriginalUnique) {
            Schema::table('controlecp_certificaciones', function (Blueprint $table) {
                $table->dropUnique(['course_id', 'momento_id']);
            });
        }

        DB::table('controlecp_certificaciones')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user as ru')
                    ->join('roles as r', 'r.id', '=', 'ru.role_id')
                    ->whereColumn('ru.user_id', 'controlecp_certificaciones.responsable_id')
                    ->where('r.slug', 'controlecp.instructor');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user as ru')
                    ->join('roles as r', 'r.id', '=', 'ru.role_id')
                    ->whereColumn('ru.user_id', 'controlecp_certificaciones.responsable_id')
                    ->where('r.slug', 'controlecp.admin');
            })
            ->update(['responsable_tipo' => 'instructor']);

        $indexes = Schema::getIndexes('controlecp_certificaciones');
        $hasRoleUnique = collect($indexes)->contains(
            fn ($index) => $index['columns'] === ['course_id', 'momento_id', 'responsable_tipo'] && $index['unique']
        );
        if (!$hasRoleUnique) {
            Schema::table('controlecp_certificaciones', function (Blueprint $table) {
                $table->unique(['course_id', 'momento_id', 'responsable_tipo'], 'certificaciones_role_unique');
            });
        }
    }

    public function down(): void
    {
        $hasMultipleRoleRecords = DB::table('controlecp_certificaciones')
            ->select('course_id', 'momento_id')
            ->groupBy('course_id', 'momento_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleRoleRecords) {
            throw new RuntimeException('Cannot roll back role-separated certifications while duplicate course/moment records exist.');
        }

        Schema::table('controlecp_certificaciones', function (Blueprint $table) {
            $table->dropUnique('certificaciones_role_unique');
            $table->dropColumn('responsable_tipo');
            $table->unique(['course_id', 'momento_id']);
        });
    }
};
