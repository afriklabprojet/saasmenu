<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: refund_policy
     * Original files: 2025_10_19_102228_create_refund_policy_table.php
     */
    public function up(): void
    {
        Schema::create('refund_policy', function (Blueprint $table) {
            $table->unique('vendor_id');
            $table->longText('refund_policy_content')->nullable();
            $table->index('vendor_id');
            $table->unique('vendor_id'); // One refund policy per vendor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_policy');
    }
};
