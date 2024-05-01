<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('xendit_logs', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('external_id', 50)->nullable();
            $table->string('user_id', 50)->nullable();
            $table->string('is_high', 15)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 15)->nullable();
            $table->string('merchant_name', 255)->nullable();
            $table->integer('amount')->nullable();
            $table->integer('paid_amount')->nullable();
            $table->string('bank_code', 50)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('payer_email', 50)->nullable();
            $table->text('description')->nullable();
            $table->integer('adjusted_received_amount')->nullable();
            $table->integer('fees_paid_amount')->nullable();
            $table->dateTime('updated')->nullable();
            $table->dateTime('created')->nullable();
            $table->string('currency', 15)->nullable();
            $table->string('payment_channel', 50)->nullable();
            $table->string('payment_destination', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xendit_logs');
    }
};
