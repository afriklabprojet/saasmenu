<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: restaurant_qr_menus
     * Purpose: Store restaurant qr menus data
     * Original migrations: 2024_10_25_100000_create_restaurant_qr_menu_tables.php
     */
    public function up(): void
    {
        Schema::create('restaurant_qr_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('qr_code_path');
            $table->string('menu_url');
            $table->json('table_numbers')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();

            // Indexes
            $table->index(['vendor_id', 'is_active']);
            $table->index('slug');

            // Foreign keys
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_qr_menus');
    }
};
