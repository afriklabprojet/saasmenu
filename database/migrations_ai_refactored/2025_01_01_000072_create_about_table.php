<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: about
     * Purpose: Store about data
     * Original migrations: 2025_10_19_100522_create_about_table.php
     */
    public function up(): void
    {
        Schema::create('about', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->longText('about_content')->nullable();

            // Indexes
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about');
    }
};
