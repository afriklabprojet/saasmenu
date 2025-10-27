<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: wishlists
     * Purpose: Store customer wishlist items
     * Original migrations: 2025_10_23_005100_create_wishlists_table.php
     */
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');

            // Indexes
            $table->index('user_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
