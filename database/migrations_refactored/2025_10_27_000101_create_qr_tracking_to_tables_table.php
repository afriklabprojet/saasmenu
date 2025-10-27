<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: qr_tracking_to_tables
     * Original files: 2025_10_23_043334_add_qr_tracking_to_tables_table.php
     */
    public function up(): void
    {
        Schema::create('qr_tracking_to_tables', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_tracking_to_tables');
    }
};
