<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: table_qr_scans
     * Purpose: Store table qr scans data
     * Original migrations: 2025_10_23_043312_create_table_qr_scans_table.php
     */
    public function up(): void
    {
        Schema::create('table_qr_scans', function (Blueprint $table) {
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('restaurant_id');
            $table->timestamp('scanned_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // Indexes
            $table->index('table_id');
            $table->index('restaurant_id');
            $table->index('scanned_at');
            $table->index(['restaurant_id', 'scanned_at']);

            // Foreign keys
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_qr_scans');
    }
};
