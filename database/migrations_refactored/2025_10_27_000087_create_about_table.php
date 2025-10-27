<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: about
     * Original files: 2025_10_19_100522_create_about_table.php
     */
    public function up(): void
    {
        Schema::create('about', function (Blueprint $table) {
            $table->unique('vendor_id');
            $table->longText('about_content')->nullable();
            $table->index('vendor_id');
            $table->unique('vendor_id'); // One about content per vendor
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
