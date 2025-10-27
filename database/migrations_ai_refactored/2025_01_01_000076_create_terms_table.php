<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: terms
     * Purpose: Store terms data
     * Original migrations: 2025_10_19_102739_create_terms_table.php
     */
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->longText('terms_content')->nullable();

            // Indexes
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
