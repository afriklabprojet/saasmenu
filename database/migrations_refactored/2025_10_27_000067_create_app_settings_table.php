<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: app_settings
     * Original files: 2025_10_18_215311_create_app_settings_table.php
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->integer('vendor_id');
            $table->string('android_link')->nullable();
            $table->string('ios_link')->nullable();
            $table->tinyInteger('mobile_app_on_off')->default(2);
            $table->string('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
