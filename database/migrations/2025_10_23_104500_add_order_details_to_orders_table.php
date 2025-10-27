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
        Schema::table('orders', function (Blueprint $table) {
            // Type de commande: 1=Livraison, 2=Sur place/Dine-in, 3=À emporter/Takeaway
            $table->tinyInteger('order_type')->default(1)->after('status_type')
                ->comment('1=delivery, 2=dine-in, 3=takeaway');
            
            // Type de paiement: 1=COD, 2=Stripe, 3=PayPal, etc.
            $table->string('payment_type', 50)->nullable()->after('order_type');
            
            // ID de transaction du paiement (Stripe, PayPal, etc.)
            $table->string('payment_id', 255)->nullable()->after('payment_type');
            
            // Date et heure de livraison
            $table->date('delivery_date')->nullable()->after('payment_id');
            $table->time('delivery_time')->nullable()->after('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'payment_type', 
                'payment_id',
                'delivery_date',
                'delivery_time'
            ]);
        });
    }
};
