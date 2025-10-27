<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: payments
     * Original files: 2022_11_11_000000_create_payments_table.php
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('payment_name');
            $table->string('payment_type');
            $table->string('environment')->nullable();
            $table->text('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('currency')->nullable();
            $table->string('image')->nullable();
            $table->text('payment_description')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('encryption_key')->nullable();
            $table->string('base_url_by_region')->nullable();
            $table->integer('is_available')->default(1);
            $table->integer('is_activate')->default(1);
            $table->integer('reorder_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
