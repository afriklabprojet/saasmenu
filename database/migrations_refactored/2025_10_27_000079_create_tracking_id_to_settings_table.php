<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: tracking_id_to_settings
     * Original files: 2025_10_19_084513_add_tracking_id_to_settings_table.php
     */
    public function up(): void
    {
        Schema::create('tracking_id_to_settings', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_id_to_settings');
    }
};
