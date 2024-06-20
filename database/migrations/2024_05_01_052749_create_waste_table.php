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
        Schema::create('wastes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['organic', 'non_organic', 'b3']);
            $table->float('weight');
            $table->float('point');
            $table->string('waste_collection_id')->nullable();
            $table->foreign('waste_collection_id')->references('id')->on('waste_collections');
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
        Schema::dropIfExists('waste_collections');
    }
};
