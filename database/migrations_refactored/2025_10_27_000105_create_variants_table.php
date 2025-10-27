<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: variants
     * Original files: 2025_10_23_110000_create_variants_table.php
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->index('item_id');
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->integer('min_order')->default(1);
            $table->integer('max_order')->default(0);
            $table->integer('low_qty')->default(0);
            $table->boolean('is_available')->default(1);
            $table->boolean('stock_management')->default(0)->comment('stck_management in model');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
