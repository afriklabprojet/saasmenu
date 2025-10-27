<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: status_columns_to_orders
     * Original files: 2025_10_23_103000_add_status_columns_to_orders_table.php
     */
    public function up(): void
    {
        Schema::create('status_columns_to_orders', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_columns_to_orders');
    }
};
