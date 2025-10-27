<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: transactions
     * Original files: 2025_10_18_201448_create_transactions_table.php
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_id')->nullable();
            $table->string('payment_type')->default('1');
            $table->enum('status', ['1', '2', '3'])->default('2');
            $table->date('expire_date')->nullable();
            $table->integer('service_limit')->default(-1);
            $table->integer('appoinment_limit')->default(-1);
            $table->text('response')->nullable();
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
