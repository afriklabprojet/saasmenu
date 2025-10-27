<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: notifications
     * Purpose: Store system notifications for users
     * Original migrations: 2024_01_15_000005_create_notifications_table.php
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('action_url')->nullable();
            $table->enum('priority')->default('medium');

            // Indexes
            $table->index(['customer_id', 'read_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
