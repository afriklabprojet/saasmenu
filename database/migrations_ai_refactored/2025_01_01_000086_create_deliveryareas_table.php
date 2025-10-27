<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: deliveryareas
     * Purpose: Store deliveryareas data
     * Original migrations: 2025_10_23_111500_create_deliveryareas_table.php
     */
    public function up(): void
    {
        Schema::create('deliveryareas', function (Blueprint $table) {
            $table->foreignId('vendor_id');
            $table->string('name');
            $table->decimal('price')->default(0);
            $table->integer('reorder_id')->default(0);

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
        Schema::dropIfExists('deliveryareas');
    }
};
