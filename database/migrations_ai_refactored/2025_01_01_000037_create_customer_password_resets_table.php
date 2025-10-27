<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: customer_password_resets
     * Purpose: Store customer password resets data
     * Original migrations: 2024_01_15_000007_create_customer_password_resets_table.php
     */
    public function up(): void
    {
        Schema::create('customer_password_resets', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_password_resets');
    }
};
