<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla puente asesoria_recursos (relación N:M entre asesorias e inventario).
     * Nota: id_asesoria se define como nullable según requerimiento.
     */
    public function up(): void
    {
        if (!Schema::hasTable('asesoria_recursos')) {
            Schema::create('asesoria_recursos', function (Blueprint $table) {
                $table->id('id_asesoria_recurso');
                $table->unsignedBigInteger('id_asesoria')->nullable();
                $table->unsignedBigInteger('id_inventario');
                $table->integer('cantidad')->default(1);
                $table->timestamps();

                $table->foreign('id_inventario')
                    ->references('id_inventario')
                    ->on('inventario')
                    ->onDelete('cascade');
            });

            return;
        }

        Schema::table('asesoria_recursos', function (Blueprint $table) {
            if (!Schema::hasColumn('asesoria_recursos', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('asesoria_recursos', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            $table->dropForeign('asesoria_recursos_id_asesoria_foreign');
            $table->unsignedBigInteger('id_asesoria')->nullable()->change();
            $table->foreign('id_asesoria')
                ->references('id_asesoria')
                ->on('asesorias')
                ->onDelete('cascade');

            $table->dropForeign('asesoria_recursos_id_inventario_foreign');
            $table->foreign('id_inventario')
                ->references('id_inventario')
                ->on('inventario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesoria_recursos');
    }
};
