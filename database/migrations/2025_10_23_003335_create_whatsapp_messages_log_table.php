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
        Schema::create('whatsapp_messages_log', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();

            // Informations du message
            $table->string('phone', 20); // Numéro de téléphone destinataire
            $table->string('message_type', 50); // order_notification, payment_confirmation, delivery_update
            $table->string('message_id', 255)->nullable(); // ID du message retourné par WhatsApp

            // Statut
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->text('error')->nullable(); // Message d'erreur en cas d'échec
            $table->string('error_code', 50)->nullable(); // Code d'erreur WhatsApp

            // Métadonnées
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Index
            $table->index('order_id');
            $table->index('restaurant_id');
            $table->index('customer_id');
            $table->index('message_id');
            $table->index('status');
            $table->index('message_type');
            $table->index(['phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages_log');
    }
};
