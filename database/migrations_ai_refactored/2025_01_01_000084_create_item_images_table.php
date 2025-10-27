<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: item_images
     * Purpose: Store item images data
     * Original migrations: 2025_10_23_110500_create_item_images_table.php
     */
    public function up(): void
    {
        Schema::create('item_images', function (Blueprint $table) {
            $table->foreignId('item_id');
            $table->string('image')->nullable();

            // Indexes
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_images');
    }
};
