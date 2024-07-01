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
        Schema::create('waste_collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->float('weight_total')->nullable();
            $table->float('point_total')->nullable();
            $table->string('address');
            $table->date('collection_date');
            $table->enum('confirmation_status', ['menunggu_konfirmasi', 'dikonfirmasi', 'berhasil'])->default('menunggu_konfirmasi');
            $table->string('created_by');
            $table->foreign('created_by')->references('id')->on('users');
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

