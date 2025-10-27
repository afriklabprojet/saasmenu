<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: missing_columns_to_settings
     * Original files: 2025_10_23_034256_add_missing_columns_to_settings_table.php
     */
    public function up(): void
    {
        Schema::create('missing_columns_to_settings', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missing_columns_to_settings');
    }
};
