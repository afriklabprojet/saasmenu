<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: customer_addresses
     * Original files: 2025_10_23_005059_create_customer_addresses_table.php
     */
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->index('user_id');
            $table->string('address_name', 100);
            $table->text('address');
            $table->string('phone', 20);
            $table->boolean('is_default')->default(false);
            $table->index(['user_id', 'is_default']);
            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
