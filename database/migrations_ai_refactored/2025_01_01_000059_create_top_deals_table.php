<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: top_deals
     * Purpose: Store top deals data
     * Original migrations: 2025_10_18_213718_create_top_deals_table.php
     */
    public function up(): void
    {
        Schema::create('top_deals', function (Blueprint $table) {
            $table->integer('vendor_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_deals');
    }
};
