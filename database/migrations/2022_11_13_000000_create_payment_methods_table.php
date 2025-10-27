<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->string('name');
            $table->text('image')->nullable();
            $table->json('credentials')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Insérer les méthodes de paiement par défaut
        $paymentMethods = [
            ['type' => 1, 'name' => 'Cash on Delivery', 'image' => '', 'status' => 'active', 'position' => 1],
            ['type' => 2, 'name' => 'Stripe', 'image' => '', 'status' => 'inactive', 'position' => 2],
            ['type' => 3, 'name' => 'Razorpay', 'image' => '', 'status' => 'inactive', 'position' => 3],
            ['type' => 4, 'name' => 'PayPal', 'image' => '', 'status' => 'inactive', 'position' => 4],
            ['type' => 5, 'name' => 'Mollie', 'image' => '', 'status' => 'inactive', 'position' => 5],
            ['type' => 6, 'name' => 'Flutterwave', 'image' => '', 'status' => 'inactive', 'position' => 6],
            ['type' => 7, 'name' => 'Paystack', 'image' => '', 'status' => 'inactive', 'position' => 7],
            ['type' => 8, 'name' => 'Mercadopago', 'image' => '', 'status' => 'inactive', 'position' => 8],
            ['type' => 9, 'name' => 'Paytab', 'image' => '', 'status' => 'inactive', 'position' => 9],
            ['type' => 10, 'name' => 'MyFatoorah', 'image' => '', 'status' => 'inactive', 'position' => 10],
            ['type' => 11, 'name' => 'ToyyibPay', 'image' => '', 'status' => 'inactive', 'position' => 11],
            ['type' => 12, 'name' => 'PhonePe', 'image' => '', 'status' => 'inactive', 'position' => 12],
            ['type' => 13, 'name' => 'Khalti', 'image' => '', 'status' => 'inactive', 'position' => 13],
            ['type' => 14, 'name' => 'Xendit', 'image' => '', 'status' => 'inactive', 'position' => 14],
            ['type' => 15, 'name' => 'SadadPay', 'image' => '', 'status' => 'inactive', 'position' => 15],
            ['type' => 16, 'name' => 'CinetPay', 'image' => '', 'status' => 'active', 'position' => 1], // CinetPay en priorité
        ];

        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')->insert([
                'type' => $method['type'],
                'name' => $method['name'],
                'image' => $method['image'],
                'status' => $method['status'],
                'position' => $method['position'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};