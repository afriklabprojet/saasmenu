<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: payment_methods
     * Original files: 2022_11_13_000000_create_payment_methods_table.php
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->integer('type');
            $table->string('name');
            $table->text('image')->nullable();
            $table->json('credentials')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('position')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
