<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: item_images
     * Original files: 2025_10_23_110500_create_item_images_table.php
     */
    public function up(): void
    {
        Schema::create('item_images', function (Blueprint $table) {
            $table->index('item_id');
            $table->string('image')->nullable();
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
