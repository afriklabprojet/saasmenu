<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: firebase_devices
     * Purpose: Store firebase devices data
     * Original migrations: 2024_01_01_000000_create_firebase_tables.php
     */
    public function up(): void
    {
        Schema::create('firebase_devices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable();
            $table->string('device_token')->unique();
            $table->enum('device_type');
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_os')->nullable();
            $table->string('app_version')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->json('topics')->nullable();
            $table->json('preferences')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->json('metadata')->nullable();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['device_type', 'is_active']);
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_devices');
    }
};
