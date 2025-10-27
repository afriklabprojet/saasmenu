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
        Schema::create('privacypolicy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('privacypolicy_content')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('vendor_id');
            $table->unique('vendor_id'); // One privacy policy per vendor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacypolicy');
    }
};
