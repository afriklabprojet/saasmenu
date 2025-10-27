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
        Schema::create('table_qr_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('restaurant_id');
            $table->timestamp('scanned_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_type', 50)->nullable(); // mobile, tablet, desktop
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();

            // Index pour performances
            $table->index('table_id');
            $table->index('restaurant_id');
            $table->index('scanned_at');
            $table->index(['restaurant_id', 'scanned_at']);

            // Clés étrangères
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_qr_scans');
    }
};
