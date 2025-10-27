<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: timings
     * Purpose: Store timings data
     * Original migrations: 2025_10_18_220433_create_timings_table.php
     */
    public function up(): void
    {
        Schema::create('timings', function (Blueprint $table) {
            $table->integer('vendor_id');
            $table->string('day');
            $table->string('open_time');
            $table->string('break_start');
            $table->string('break_end');
            $table->string('close_time');
            $table->tinyInteger('is_always_close')->comment('1 For Yes, 2 For No');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timings');
    }
};
