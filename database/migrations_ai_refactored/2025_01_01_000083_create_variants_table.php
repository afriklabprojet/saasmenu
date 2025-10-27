<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: variants
     * Purpose: Store variants data
     * Original migrations: 2025_10_23_110000_create_variants_table.php
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->foreignId('item_id');
            $table->string('name')->nullable();
            $table->decimal('price')->default(0);
            $table->decimal('original_price')->default(0);
            $table->integer('qty')->default(0);
            $table->integer('min_order')->default(1);
            $table->integer('max_order')->default(0);
            $table->integer('low_qty')->default(0);
            $table->boolean('is_available')->default(1);
            $table->boolean('stock_management')->default(0)->comment('stck_management in model');

            // Indexes
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
