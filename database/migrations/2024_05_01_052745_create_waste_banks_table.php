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
        Schema::create('waste_banks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('address');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->decimal('longitude', 13, 10);
            $table->decimal('latitude', 12, 10);
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
        Schema::dropIfExists('waste_banks');
    }
};

