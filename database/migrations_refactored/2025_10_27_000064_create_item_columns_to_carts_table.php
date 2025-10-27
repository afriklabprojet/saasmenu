<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: item_columns_to_carts
     * Original files: 2025_10_18_211744_add_item_columns_to_carts_table.php
     */
    public function up(): void
    {
        Schema::create('item_columns_to_carts', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_columns_to_carts');
    }
};
