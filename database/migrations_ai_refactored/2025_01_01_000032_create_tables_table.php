<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: tables
     * Purpose: Store restaurant table information for dining
     * Original migrations: 2024_01_15_000002_create_tables_table.php, 2025_10_23_043334_add_qr_tracking_to_tables_table.php, 2025_10_23_043334_add_qr_tracking_to_tables_table.php
     */
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->foreignId('restaurant_id');
            $table->string('table_number');
            $table->string('name')->nullable();
            $table->integer('capacity')->default(4);
            $table->string('location')->nullable();
            $table->string('table_code')->unique();
            $table->string('qr_code_path')->nullable();
            $table->enum('status')->default('active');
            $table->timestamp('last_accessed')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->string('qr_color_fg')->default('#000000')->comment('Couleur avant-plan QR code');
            $table->string('qr_color_bg')->default('#FFFFFF')->comment('Couleur arrière-plan QR code');
            $table->boolean('qr_use_logo')->default(true)->comment('Utiliser logo restaurant dans QR');
            $table->unsignedInteger('qr_size')->default(300)->comment('Taille QR code en pixels');

            // Indexes
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'table_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
