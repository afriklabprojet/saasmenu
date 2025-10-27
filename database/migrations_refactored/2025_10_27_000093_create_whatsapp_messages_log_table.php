<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: whatsapp_messages_log
     * Original files: 2025_10_23_003335_create_whatsapp_messages_log_table.php
     */
    public function up(): void
    {
        Schema::create('whatsapp_messages_log', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('restaurant_id');
            $table->index('customer_id');
            $table->string('phone', 20);
            $table->index('message_type');
            $table->index('message_id');
            $table->index('status');
            $table->text('error')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->index(['phone', 'created_at']);
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
