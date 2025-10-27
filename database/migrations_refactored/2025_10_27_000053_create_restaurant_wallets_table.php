<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: restaurant_wallets
     * Original files: 2025_10_17_000002_create_wallet_system.php
     */
    public function up(): void
    {
        Schema::create('restaurant_wallets', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->index(['vendor_id', 'balance']);
            $table->index(['vendor_id', 'balance']);
            $table->foreign('vendor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_wallets');
    }
};
