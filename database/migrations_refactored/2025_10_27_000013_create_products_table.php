<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: products
     * Original files: 2022_11_11_105804_create_products_table.php, 2022_11_12_105804_update_products_table.php
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('slug');
            $table->double('price');
            $table->string('description');
            $table->boolean('is_available')->comment('1="yes",2="no"')->default('1');
            $table->boolean('is_deleted')->comment('1="yes",2="no"')->default('2');
            $table->integer('sub_category_id')->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
