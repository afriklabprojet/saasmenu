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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->enum('type', [
                'welcome_bonus',
                'order_purchase',
                'referral_bonus',
                'birthday_bonus',
                'challenge_completion',
                'admin_adjustment',
                'reward_redemption',
                'points_expiry',
                'tier_upgrade_bonus'
            ]);
            $table->integer('points');
            $table->integer('balance_after');
            $table->text('description');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['member_id', 'created_at']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['type']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
