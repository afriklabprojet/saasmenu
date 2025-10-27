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
        Schema::create('pixcel_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('facebook_pixcel_id')->nullable();
            $table->string('twitter_pixcel_id')->nullable();
            $table->string('linkedin_pixcel_id')->nullable();
            $table->string('googletag_pixcel_id')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('vendor_id');
            $table->unique('vendor_id'); // One pixel setting per vendor
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pixcel_settings');
    }
};
