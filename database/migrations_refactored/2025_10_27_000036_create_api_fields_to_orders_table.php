<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: api_fields_to_orders
     * Original files: 2024_01_15_000006_add_api_fields_to_orders_table.php
     */
    public function up(): void
    {
        Schema::create('api_fields_to_orders', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_fields_to_orders');
    }
};
