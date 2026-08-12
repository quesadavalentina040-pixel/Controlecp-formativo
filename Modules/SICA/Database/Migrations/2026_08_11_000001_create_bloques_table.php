<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabla de Bloques / Procesos Principales (Estratégicos, Misionales, Apoyo)
        if (!Schema::hasTable('bloques')) {
            Schema::create('bloques', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->integer('order_index')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Asociar apps/submódulos existentes con el bloque correspondiente
        if (Schema::hasTable('apps') && !Schema::hasColumn('apps', 'bloque_id')) {
            Schema::table('apps', function (Blueprint $table) {
                $table->foreignId('bloque_id')->nullable()->after('id')->constrained('bloques')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('apps') && Schema::hasColumn('apps', 'bloque_id')) {
            Schema::table('apps', function (Blueprint $table) {
                $table->dropForeign(['bloque_id']);
                $table->dropColumn('bloque_id');
            });
        }
        Schema::dropIfExists('bloques');
    }
};
