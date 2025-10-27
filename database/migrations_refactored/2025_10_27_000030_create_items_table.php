<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: items
     * Original files: 2024_01_15_000001_create_items_table.php, 2025_10_18_202418_create_items_table.php
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('cat_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(1);
            $table->boolean('is_deleted')->default(0);
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
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
