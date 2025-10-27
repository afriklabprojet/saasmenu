<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: promotionalbanner
     * Purpose: Store promotionalbanner data
     * Original migrations: 2025_10_19_085645_create_promotionalbanner_table.php
     */
    public function up(): void
    {
        Schema::create('promotionalbanner', function (Blueprint $table) {
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('image');

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
        Schema::dropIfExists('promotionalbanner');
    }
};
