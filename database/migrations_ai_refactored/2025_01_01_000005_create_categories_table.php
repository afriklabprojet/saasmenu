<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: categories
     * Purpose: Store item categories for organizing menus
     * Original migrations: 2022_09_28_105405_create_categories_table.php, 2025_10_18_204236_add_reorder_id_to_categories_table.php, 2025_10_18_204236_add_reorder_id_to_categories_table.php
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('name');
            $table->string('slug');
            $table->string('image');
            $table->boolean('is_available')->default('1')->comment('1--> yes, 2-->No');
            $table->boolean('is_deleted')->default('2')->comment('1--> yes, 2-->No');
            $table->integer('reorder_id')->default(0);
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
