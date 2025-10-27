<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: categories
     * Original files: 2022_09_28_105405_create_categories_table.php
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('name');
            $table->string('slug');
            $table->string('image');
            $table->boolean('is_available')->comment('1--> yes, 2-->No')->default('1');
            $table->boolean('is_deleted')->comment('1--> yes, 2-->No')->default('2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
