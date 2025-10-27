<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table des wallets des restaurants
        Schema::create('restaurant_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            $table->decimal('balance', 15, 2)->default(0); // Solde disponible
            $table->decimal('pending_balance', 15, 2)->default(0); // En attente
            $table->decimal('total_earnings', 15, 2)->default(0); // Total généré
            $table->decimal('total_withdrawn', 15, 2)->default(0); // Total retiré
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'balance']);
        });

        // Table des transactions wallet
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('transaction_id')->unique();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->string('source'); // 'order', 'withdrawal', 'commission', 'refund'
            $table->unsignedBigInteger('reference_id')->nullable(); // order_id, withdrawal_id
            $table->string('description');
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'type', 'status']);
        });

        // Table des moyens de retrait des restaurants
        Schema::create('withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('type'); // 'orange_money', 'mtn_money', 'moov_money', 'bank', 'cinetpay'
            $table->string('account_number');
            $table->string('account_name');
            $table->json('additional_info')->nullable(); // Infos supplémentaires
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'type', 'is_active']);
        });

        // Table des demandes de retrait
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('request_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->unsignedBigInteger('withdrawal_method_id');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
            $table->string('provider_transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users');
            $table->foreign('withdrawal_method_id')->references('id')->on('withdrawal_methods');
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('withdrawal_methods');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('restaurant_wallets');
    }
};
