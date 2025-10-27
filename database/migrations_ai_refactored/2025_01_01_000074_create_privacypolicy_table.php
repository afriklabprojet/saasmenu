<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: privacypolicy
     * Purpose: Store privacypolicy data
     * Original migrations: 2025_10_19_101651_create_privacypolicy_table.php
     */
    public function up(): void
    {
        Schema::create('privacypolicy', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->longText('privacypolicy_content')->nullable();

            // Indexes
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacypolicy');
    }
};
