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
        Schema::create('systemaddons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_identifier');
            $table->string('version', 20);
            $table->integer('activated');
            $table->string('image');
            $table->integer('type')->nullable();
            $table->timestamps();

            // Index sur unique_identifier pour les recherches fréquentes
            $table->index('unique_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systemaddons');
    }
};
