<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timings', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('day', 50);
            $table->string('open_time', 30);
            $table->string('break_start');
            $table->string('break_end');
            $table->string('close_time', 30);
            $table->tinyInteger('is_always_close')->comment('1 For Yes, 2 For No');
            $table->timestamps();
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
