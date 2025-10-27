<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: carts
     * Original files: 2022_12_05_114128_create_carts_table.php
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
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
