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
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['points', 'visits', 'spend'])->default('points');
            $table->decimal('points_per_currency', 8, 2)->default(1.00); // Points par unité monétaire
            $table->decimal('currency_per_point', 8, 2)->default(0.01); // Valeur monétaire par point
            $table->integer('min_points_redemption')->default(100);
            $table->integer('points_expiry_months')->nullable(); // null = jamais expire
            $table->json('tiers')->nullable(); // Configuration des niveaux
            $table->json('rules')->nullable(); // Règles personnalisées
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');

            $table->index(['restaurant_id', 'is_active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
