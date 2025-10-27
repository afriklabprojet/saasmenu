<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: table_book
     * Original files: 2025_10_23_112000_create_table_book_table.php
     */
    public function up(): void
    {
        Schema::create('table_book', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->integer('total_members')->default(1);
            $table->index('booking_date');
            $table->time('booking_time');
            $table->text('message')->nullable();
            $table->index('status');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_book');
    }
};
