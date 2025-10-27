<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: restaurant_wallets
     * Purpose: Store restaurant wallets data
     * Original migrations: 2025_10_17_000002_create_wallet_system.php
     */
    public function up(): void
    {
        Schema::create('restaurant_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->unique();
            $table->decimal('balance')->default(0);
            $table->decimal('pending_balance')->default(0);
            $table->decimal('total_earnings')->default(0);
            $table->decimal('total_withdrawn')->default(0);

            // Indexes
            $table->index(['vendor_id', 'balance']);

            // Foreign keys
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
