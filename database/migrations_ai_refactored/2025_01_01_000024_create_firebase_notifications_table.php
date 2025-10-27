<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: firebase_notifications
     * Purpose: Store firebase notifications data
     * Original migrations: 2024_01_01_000000_create_firebase_tables.php
     */
    public function up(): void
    {
        Schema::create('firebase_notifications', function (Blueprint $table) {
            $table->foreign('campaign_id');
            $table->foreign('template_id');
            $table->foreign('automation_id');

            // Foreign keys
            $table->foreign('campaign_id')->references('id')->on('firebase_campaigns')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('firebase_templates')->onDelete('set null');
            $table->foreign('automation_id')->references('id')->on('firebase_automations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_notifications');
    }
};
