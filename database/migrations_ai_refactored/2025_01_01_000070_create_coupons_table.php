<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: coupons
     * Purpose: Store discount coupons and offers
     * Original migrations: 2025_10_19_093148_create_coupons_table.php
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type');
            $table->decimal('price');
            $table->datetime('active_from');
            $table->datetime('active_to');
            $table->integer('limit')->default(0);
            $table->integer('reorder_id')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_deleted')->default(false);

            // Indexes
            $table->index('vendor_id');
            $table->index('reorder_id');
            $table->index(['is_available', 'is_deleted']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
