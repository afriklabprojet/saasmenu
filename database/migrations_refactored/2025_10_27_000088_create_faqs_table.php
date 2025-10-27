<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: faqs
     * Original files: 2025_10_19_100920_create_faqs_table.php
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->string('question')->nullable();
            $table->text('answer')->nullable();
            $table->index('reorder_id');
            $table->index('vendor_id');
            $table->index('reorder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
