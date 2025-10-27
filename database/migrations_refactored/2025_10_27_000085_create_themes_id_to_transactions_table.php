<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: themes_id_to_transactions
     * Original files: 2025_10_19_095148_add_themes_id_to_transactions_table.php
     */
    public function up(): void
    {
        Schema::create('themes_id_to_transactions', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes_id_to_transactions');
    }
};
