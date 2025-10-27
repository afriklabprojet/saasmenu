<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: banner_image_to_banners
     * Original files: 2025_10_18_205643_add_banner_image_to_banners_table.php
     */
    public function up(): void
    {
        Schema::create('banner_image_to_banners', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_image_to_banners');
    }
};
