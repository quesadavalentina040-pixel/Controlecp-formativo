<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->foreignId('productive_unit_warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('element_id')->constrained()->onDelete('cascade');
            $table->enum('destination', [
                'Producción',
                'Formación'
            ]);
            $table->text('description')->nullable();
            $table->integer('price');
            $table->decimal('amount', 10, 2);
            $table->decimal('stock', 10, 2);
            $table->date('production_date')->nullable();
            $table->integer('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->enum('state', [
                'Disponible',
                'No disponible'
            ])->default('Disponible');
            $table->string('mark')->nullable();
            $table->unsignedInteger('inventory_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inventories');
    }
}
