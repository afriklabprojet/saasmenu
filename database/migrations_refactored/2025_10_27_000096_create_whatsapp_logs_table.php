<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: whatsapp_logs
     * Original files: 2025_10_23_015418_create_whatsapp_logs_table.php
     */
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->string('to', 20)->index()->comment('Numéro destinataire');
            $table->text('message')->comment('Contenu du message');
            $table->string('status', 100)->default('pending')->comment('Statut de l\'envoi');
            $table->boolean('success')->default(false)->index()->comment('Succès ou échec');
            $table->string('message_id', 100)->nullable()->comment('ID WhatsApp du message');
            $table->json('response')->nullable()->comment('Réponse de l\'API WhatsApp');
            $table->json('context')->nullable()->comment('Contexte additionnel (order_id, etc.)');
            $table->timestamp('sent_at')->nullable()->comment('Date et heure d\'envoi');
            $table->index('created_at');
            $table->index(['success', 'created_at']);
            $table->index('created_at');
            $table->index(['success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
