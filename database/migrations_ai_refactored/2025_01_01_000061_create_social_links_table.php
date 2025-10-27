<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: social_links
     * Purpose: Store social links data
     * Original migrations: 2025_10_18_215637_create_social_links_table.php
     */
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->integer('vendor_id');
            $table->text('icon');
            $table->text('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
