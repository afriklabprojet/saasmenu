<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: order_details
     * Purpose: Store individual items within orders
     * Original migrations: 2022_12_10_051230_create_order_details_table.php
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable();
            $table->text('session_id')->nullable();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image');
            $table->string('attribute')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('variation_name')->nullable();
            $table->double('product_price');
            $table->double('product_tax');
            $table->integer('qty')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
