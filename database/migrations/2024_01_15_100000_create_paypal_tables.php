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
        // Table des transactions PayPal
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('paypal_payment_id')->unique();
            $table->string('paypal_order_id')->nullable();
            $table->string('payer_id')->nullable();

            $table->enum('type', [
                'express_checkout',
                'direct_credit_card',
                'subscription',
                'billing_agreement',
                'refund'
            ])->default('express_checkout');

            $table->enum('status', [
                'created',
                'approved',
                'completed',
                'failed',
                'cancelled',
                'denied',
                'pending',
                'refunded'
            ])->default('created');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->decimal('net_amount', 10, 2)->nullable();

            // Informations de remboursement
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'completed', 'failed'])->nullable();

            // Détails de la transaction
            $table->json('transaction_details')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['paypal_payment_id']);
            $table->index(['status', 'created_at']);
        });

        // Table des abonnements PayPal
        Schema::create('paypal_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('paypal_subscription_id')->unique();
            $table->string('plan_id');

            $table->enum('status', [
                'active',
                'cancelled',
                'suspended',
                'expired',
                'pending',
                'approval_pending'
            ])->default('pending');

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Cycle de facturation
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('billing_frequency')->default(1);

            // Dates importantes
            $table->timestamp('start_date')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Statistiques
            $table->integer('failure_count')->default(0);

            // Détails
            $table->json('subscription_details')->nullable();
            $table->json('webhook_data')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['paypal_subscription_id']);
            $table->index(['status', 'next_billing_date']);
        });

        // Table des plans PayPal
        Schema::create('paypal_plans', function (Blueprint $table) {
            $table->id();
            $table->string('paypal_plan_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Configuration du cycle
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('billing_frequency')->default(1);

            // URLs de retour
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();

            // Paramètres avancés
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->integer('max_fail_attempts')->default(3);
            $table->boolean('auto_bill_amount')->default(true);

            // Détails du plan
            $table->json('plan_details')->nullable();

            $table->timestamps();

            $table->index(['paypal_plan_id']);
            $table->index(['is_active']);
        });

        // Table des webhooks PayPal
        Schema::create('paypal_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id')->nullable();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->enum('status', ['received', 'processed', 'failed'])->default('received');

            // Données de l'événement
            $table->json('event_data');
            $table->timestamp('event_time');

            // Traitement
            $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            $table->index(['event_id']);
            $table->index(['event_type', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Table des disputes PayPal
        Schema::create('paypal_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_id')->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('paypal_transactions')->onDelete('set null');

            $table->enum('status', [
                'open',
                'waiting_for_buyer_response',
                'waiting_for_seller_response',
                'under_paypal_review',
                'resolved',
                'other'
            ]);

            $table->enum('reason', [
                'merchandise_or_service_not_received',
                'merchandise_or_service_not_as_described',
                'unauthorized',
                'credit_not_processed',
                'cancelled_recurring_billing',
                'problem_with_remittance',
                'other'
            ]);

            $table->decimal('dispute_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Dates importantes
            $table->timestamp('dispute_time');
            $table->timestamp('respond_by_date')->nullable();

            // Communication
            $table->json('messages')->nullable();
            $table->json('evidence_documents')->nullable();

            // Résolution
            $table->enum('outcome', ['resolved_buyer_favour', 'resolved_seller_favour', 'resolved_with_payout'])->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['dispute_id']);
            $table->index(['status', 'respond_by_date']);
            $table->index(['transaction_id']);
        });

        // Table des paramètres PayPal
        Schema::create('paypal_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');

            // Configuration PayPal
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Encrypted
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');
            $table->string('webhook_url')->nullable();
            $table->string('webhook_id')->nullable();

            // Paramètres de paiement
            $table->string('currency', 3)->default('EUR');
            $table->boolean('enabled')->default(false);
            $table->boolean('express_checkout_enabled')->default(true);
            $table->boolean('credit_card_enabled')->default(true);
            $table->boolean('subscriptions_enabled')->default(false);

            // URLs par défaut
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();

            // Configuration des frais
            $table->decimal('transaction_fee_percentage', 5, 2)->default(0);
            $table->decimal('transaction_fee_fixed', 10, 2)->default(0);

            // Dernière synchronisation
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_status')->nullable();

            $table->timestamps();

            $table->index(['restaurant_id']);
            $table->index(['enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_settings');
        Schema::dropIfExists('paypal_disputes');
        Schema::dropIfExists('paypal_webhooks');
        Schema::dropIfExists('paypal_plans');
        Schema::dropIfExists('paypal_subscriptions');
        Schema::dropIfExists('paypal_transactions');
    }
};
