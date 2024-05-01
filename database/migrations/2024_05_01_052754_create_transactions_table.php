<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('transaction_date');
            $table->string('transaction_type');
            $table->integer('total_balance_involved');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('description')->nullable();
            $table->string('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('xendit_id')->nullable();
            $table->foreign('xendit_id')->references('id')->on('xendit_logs');
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
        Schema::dropIfExists('transactions');
    }
}

