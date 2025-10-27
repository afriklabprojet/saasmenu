<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: push_subscriptions
     * Purpose: Store push subscriptions data
     * Original migrations: 2025_10_17_162141_create_push_subscriptions_table.php
     */
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('endpoint');
            $table->string('auth_key');
            $table->string('p256dh_key');
            $table->boolean('is_active')->default(true);
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();

            // Foreign keys
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
