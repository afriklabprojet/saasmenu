<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: device_tokens
     * Original files: 2024_01_01_000003_create_device_tokens_table.php
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('device_token', 500);
            $table->index('device_type');
            $table->json('device_info')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('last_used_at');
            $table->index(['user_id', 'is_active']);
            $table->unique(['user_id', 'device_token']);
            $table->index(['user_id', 'is_active']);
            $table->index('device_type');
            $table->index('last_used_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
