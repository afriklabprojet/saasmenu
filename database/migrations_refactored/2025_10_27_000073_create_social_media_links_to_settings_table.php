<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: social_media_links_to_settings
     * Original files: 2025_10_19_075700_add_social_media_links_to_settings_table.php
     */
    public function up(): void
    {
        Schema::create('social_media_links_to_settings', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_links_to_settings');
    }
};
