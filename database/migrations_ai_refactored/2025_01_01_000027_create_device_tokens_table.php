<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: device_tokens
     * Purpose: Store device tokens data
     * Original migrations: 2024_01_01_000003_create_device_tokens_table.php
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('device_token');
            $table->string('device_type')->default('unknown');
            $table->json('device_info')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index('device_type');
            $table->index('last_used_at');

            // Foreign keys
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
