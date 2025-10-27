<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: table_qr_scans
     * Original files: 2025_10_23_043312_create_table_qr_scans_table.php
     */
    public function up(): void
    {
        Schema::create('table_qr_scans', function (Blueprint $table) {
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('scanned_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->index(['restaurant_id', 'scanned_at']);
            $table->index('table_id');
            $table->index('restaurant_id');
            $table->index('scanned_at');
            $table->index(['restaurant_id', 'scanned_at']);
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
