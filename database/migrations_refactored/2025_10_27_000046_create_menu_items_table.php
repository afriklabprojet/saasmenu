<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: menu_items
     * Original files: 2024_01_15_000018_create_menu_items_table.php
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->index('is_featured');
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->json('allergens')->nullable();
            $table->json('nutritional_info')->nullable();
            $table->string('preparation_time')->nullable();
            $table->index('barcode');
            $table->index('sku');
            $table->boolean('track_inventory')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->json('modifiers')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->index('sort_order');
            $table->index(['restaurant_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
            $table->index('barcode');
            $table->index('sku');
            $table->index('sort_order');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
