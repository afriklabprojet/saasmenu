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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('features')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration')->default(30); // en jours
            $table->integer('service_limit')->default(-1); // -1 = illimité
            $table->integer('appoinment_limit')->default(-1); // -1 = illimité
            $table->enum('type', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->boolean('is_available')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
