<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: footerfeatures
     * Purpose: Store footerfeatures data
     * Original migrations: 2022_12_01_053128_create_footerfeatures_table.php
     */
    public function up(): void
    {
        Schema::create('footerfeatures', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('icon');
            $table->text('title');
            $table->text('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footerfeatures');
    }
};
