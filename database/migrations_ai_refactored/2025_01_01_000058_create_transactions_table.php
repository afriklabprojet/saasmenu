<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: transactions
     * Purpose: Store transactions data
     * Original migrations: 2025_10_18_201448_create_transactions_table.php, 2025_10_19_091908_add_purchase_date_to_transactions_table.php, 2025_10_19_091908_add_purchase_date_to_transactions_table.php, 2025_10_19_095148_add_themes_id_to_transactions_table.php, 2025_10_19_095148_add_themes_id_to_transactions_table.php
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('amount')->default(0);
            $table->string('payment_id')->nullable();
            $table->string('payment_type')->default('1');
            $table->enum('status')->default('2');
            $table->date('expire_date')->nullable();
            $table->integer('service_limit')->default(-1);
            $table->integer('appoinment_limit')->default(-1);
            $table->text('response')->nullable();
            $table->date('purchase_date')->nullable();
            $table->unsignedBigInteger('themes_id')->nullable();

            // Indexes
            $table->index('themes_id');

            // Foreign keys
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
