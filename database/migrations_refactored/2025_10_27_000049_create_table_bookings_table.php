<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: table_bookings
     * Original files: 2024_01_15_000025_create_table_bookings_table.php
     */
    public function up(): void
    {
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->integer('guests_count');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->text('special_requests')->nullable();
            $table->index('status');
            $table->text('admin_notes')->nullable();
            $table->index(['vendor_id', 'booking_date', 'booking_time']);
            $table->index(['vendor_id', 'booking_date', 'booking_time']);
            $table->index('status');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_bookings');
    }
};
