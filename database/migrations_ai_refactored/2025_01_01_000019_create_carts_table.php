<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: carts
     * Purpose: Store shopping cart items for customers
     * Original migrations: 2022_12_05_114128_create_carts_table.php, 2025_10_18_211744_add_item_columns_to_carts_table.php, 2025_10_18_211744_add_item_columns_to_carts_table.php, 2025_10_18_214515_add_buynow_to_carts_table.php, 2025_10_18_214515_add_buynow_to_carts_table.php
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable()->default(0);
            $table->text('session_id')->nullable()->default('');
            $table->integer('product_id');
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image');
            $table->string('attribute')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('variation_name')->nullable();
            $table->integer('qty')->default(1);
            $table->double('product_price');
            $table->double('product_tax');
            $table->integer('item_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_image')->nullable();
            $table->double('item_price')->nullable();
            $table->integer('extras_id')->nullable();
            $table->string('extras_name')->nullable();
            $table->double('extras_price')->nullable();
            $table->double('price')->nullable();
            $table->double('tax')->nullable();
            $table->integer('variants_id')->nullable();
            $table->string('variants_name')->nullable();
            $table->double('variants_price')->nullable();
            $table->boolean('buynow')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
