<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: global_extras
     * Purpose: Store global extras data
     * Original migrations: 2025_10_23_111000_create_global_extras_table.php
     */
    public function up(): void
    {
        Schema::create('global_extras', function (Blueprint $table) {
            $table->foreignId('vendor_id');
            $table->string('name');
            $table->decimal('price')->default(0);
            $table->integer('reorder_id')->default(0);
            $table->boolean('is_available')->default(1);

            // Indexes
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
