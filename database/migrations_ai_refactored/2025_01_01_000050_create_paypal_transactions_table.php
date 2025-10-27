<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: paypal_transactions
     * Purpose: Store paypal transactions data
     * Original migrations: 2024_01_15_100000_create_paypal_tables.php
     */
    public function up(): void
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->string('paypal_payment_id')->unique();
            $table->string('paypal_order_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->decimal('amount');
            $table->string('currency')->default('EUR');
            $table->decimal('fee_amount')->nullable();
            $table->decimal('net_amount')->nullable();
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount')->nullable();
            $table->enum('refund_status')->nullable();
            $table->json('transaction_details')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['paypal_payment_id']);
            $table->index(['status', 'created_at']);

            // Foreign keys
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
