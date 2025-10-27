<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: vendor_id_to_blogs
     * Original files: 2025_10_18_211317_add_vendor_id_to_blogs_table.php
     */
    public function up(): void
    {
        Schema::create('vendor_id_to_blogs', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_id_to_blogs');
    }
};
