<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: whatsapp_logs
     * Purpose: Store whatsapp logs data
     * Original migrations: 2025_10_23_015418_create_whatsapp_logs_table.php
     */
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->string('to')->comment('Numéro destinataire');
            $table->text('message')->comment('Contenu du message');
            $table->string('status')->default('pending');
            $table->boolean('success')->default(false)->comment('Succès ou échec');
            $table->string('message_id')->nullable()->comment('ID WhatsApp du message');
            $table->json('response')->nullable();
            $table->json('context')->nullable()->comment('Contexte additionnel (order_id, etc.)');
            $table->timestamp('sent_at')->nullable();
            $table->index('created_at');
            $table->timestamps();

            // Indexes
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
