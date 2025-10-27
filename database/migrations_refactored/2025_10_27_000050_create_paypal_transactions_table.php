<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: paypal_transactions
     * Original files: 2024_01_15_100000_create_paypal_tables.php
     */
    public function up(): void
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('paypal_payment_id')->unique();
            $table->string('paypal_order_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->decimal('net_amount', 10, 2)->nullable();
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'completed', 'failed'])->nullable();
            $table->json('transaction_details')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->index(['order_id', 'status']);
            $table->index(['paypal_payment_id']);
            $table->index(['status', 'created_at']);
            $table->index(['order_id', 'status']);
            $table->index(['paypal_payment_id']);
            $table->index(['status', 'created_at']);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_transactions');
    }
};
