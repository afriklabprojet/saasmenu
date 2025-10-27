<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: promotionalbanner
     * Original files: 2025_10_19_085645_create_promotionalbanner_table.php
     */
    public function up(): void
    {
        Schema::create('promotionalbanner', function (Blueprint $table) {
            $table->index('reorder_id');
            $table->index('vendor_id');
            $table->string('image', 255);
            $table->index('vendor_id');
            $table->index('reorder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotionalbanner');
    }
};
