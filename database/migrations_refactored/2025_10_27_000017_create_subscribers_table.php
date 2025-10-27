<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: subscribers
     * Original files: 2022_12_01_085740_create_subscribers_table.php
     */
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
