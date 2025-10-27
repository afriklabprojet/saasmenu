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
        Schema::create('store_category', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id');
            $table->string('name');
            $table->integer('is_available')->default(1)->comment('1=Yes,2=No');
            $table->integer('is_deleted')->default(2)->comment('1=Yes,2=No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_category');
    }
};
