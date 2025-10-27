<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: items
     * Purpose: Store menu items/products for restaurants
     * Original migrations: 2024_01_15_000001_create_items_table.php, 2025_10_18_202418_create_items_table.php
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            
            // Categories and organization
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('category_id');
            $table->integer('reorder_id')->default(0);
            
            // Item properties
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('has_variants')->default(false);
            $table->boolean('has_extras')->default(false);
            $table->enum('item_type', ['veg', 'non_veg', 'egg'])->default('veg');
            
            // Stock management
            $table->integer('stock_qty')->nullable();
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_unlimited')->default(true);
            
            // SEO and meta
            $table->string('slug')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            
            // Nutritional info
            $table->decimal('calories', 8, 2)->nullable();
            $table->text('ingredients')->nullable();
            $table->text('allergens')->nullable();
            
            // Timing and availability
            $table->time('available_from')->nullable();
            $table->time('available_until')->nullable();
            $table->json('available_days')->nullable(); // [1,2,3,4,5,6,7] for days
            
            $table->timestamps();
            
            // Indexes
            $table->index(['vendor_id', 'category_id', 'is_available']);
            $table->index(['is_featured', 'is_available']);
            $table->index('reorder_id');
            $table->index('slug');
            
            // Foreign keys
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};