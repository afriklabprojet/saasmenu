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
        Schema::create('promotionalbanner', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('image', 255);
            $table->timestamps();

            // Add index for vendor_id for better performance
            $table->index('vendor_id');
            $table->index('reorder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotionalbanner');
    }
};
