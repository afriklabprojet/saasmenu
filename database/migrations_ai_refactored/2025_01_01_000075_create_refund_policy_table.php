<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: refund_policy
     * Purpose: Store refund policy data
     * Original migrations: 2025_10_19_102228_create_refund_policy_table.php
     */
    public function up(): void
    {
        Schema::create('refund_policy', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->longText('refund_policy_content')->nullable();

            // Indexes
            $table->index('vendor_id');
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
