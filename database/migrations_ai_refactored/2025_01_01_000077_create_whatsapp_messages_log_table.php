<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: whatsapp_messages_log
     * Purpose: Store whatsapp messages log data
     * Original migrations: 2025_10_23_003335_create_whatsapp_messages_log_table.php
     */
    public function up(): void
    {
        Schema::create('whatsapp_messages_log', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('phone');
            $table->string('message_type');
            $table->string('message_id')->nullable();
            $table->enum('status')->default('pending');
            $table->text('error')->nullable();
            $table->string('error_code')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            // Indexes
            $table->index('order_id');
            $table->index('restaurant_id');
            $table->index('customer_id');
            $table->index('message_id');
            $table->index('status');
            $table->index('message_type');
            $table->index(['phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages_log');
    }
};
