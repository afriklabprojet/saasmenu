<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: order_details_to_orders
     * Original files: 2025_10_23_104500_add_order_details_to_orders_table.php
     */
    public function up(): void
    {
        Schema::create('order_details_to_orders', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details_to_orders');
    }
};
