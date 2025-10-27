<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: purchase_date_to_transactions
     * Original files: 2025_10_19_091908_add_purchase_date_to_transactions_table.php
     */
    public function up(): void
    {
        Schema::create('purchase_date_to_transactions', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_date_to_transactions');
    }
};
