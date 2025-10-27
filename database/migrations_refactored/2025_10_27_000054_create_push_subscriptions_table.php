<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: push_subscriptions
     * Original files: 2025_10_17_162141_create_push_subscriptions_table.php
     */
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('endpoint', 500);
            $table->string('auth_key');
            $table->string('p256dh_key');
            $table->boolean('is_active')->default(true);
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unique(['user_id', 'endpoint'], 'user_endpoint_unique');
            $table->unique(['user_id', 'endpoint'], 'user_endpoint_unique');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
