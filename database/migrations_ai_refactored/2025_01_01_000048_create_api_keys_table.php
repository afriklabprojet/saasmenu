<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: api_keys
     * Purpose: Store api keys data
     * Original migrations: 2024_01_15_000023_create_api_keys_table.php
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->string('name');
            $table->string('hashed_key')->unique();
            $table->json('permissions')->nullable();
            $table->foreignId('restaurant_id')->nullable();
            $table->foreignId('user_id');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Indexes
            $table->index(['hashed_key', 'is_active']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
