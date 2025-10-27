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
        Schema::create('area', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->unsignedBigInteger('city_id');
            $table->string('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('city')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};
