<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: global_extras
     * Original files: 2025_10_23_111000_create_global_extras_table.php
     */
    public function up(): void
    {
        Schema::create('global_extras', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->index('reorder_id');
            $table->boolean('is_available')->default(1);
            $table->index('vendor_id');
            $table->index('reorder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_extras');
    }
};
