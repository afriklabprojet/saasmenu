<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: buynow_to_carts
     * Original files: 2025_10_18_214515_add_buynow_to_carts_table.php
     */
    public function up(): void
    {
        Schema::create('buynow_to_carts', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buynow_to_carts');
    }
};
