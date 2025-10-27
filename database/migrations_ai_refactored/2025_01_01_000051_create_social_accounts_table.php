<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: social_accounts
     * Purpose: Store social accounts data
     * Original migrations: 2024_01_15_200000_create_social_login_tables.php
     */
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->timestamp('provider_expires_at')->nullable();
            $table->string('avatar')->nullable();
            $table->json('profile_data')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index(['user_id', 'provider']);
            $table->index(['provider', 'is_active']);
            $table->index(['last_login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
