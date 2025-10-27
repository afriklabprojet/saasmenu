<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: reorder_id_to_items
     * Original files: 2025_10_18_204335_add_reorder_id_to_items_table.php
     */
    public function up(): void
    {
        Schema::create('reorder_id_to_items', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reorder_id_to_items');
    }
};
