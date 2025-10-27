<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedDefaultPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Créer un utilisateur vendor par défaut s'il n'existe pas
        $defaultVendor = DB::table('users')->where('type', 1)->first();

        if (!$defaultVendor) {
            $vendorId = DB::table('users')->insertGetId([
                'name' => 'Default Restaurant',
                'slug' => 'default-restaurant',
                'email' => 'admin@restaurant.com',
                'password' => bcrypt('password'),
                'type' => 1, // vendor
                'mobile' => '+225 07 00 00 00 00',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $vendorId = $defaultVendor->id;
        }

        // Moyens de paiement par défaut
        $defaultPayments = [
            [
                'vendor_id' => $vendorId,
                'payment_name' => 'Cash On Delivery',
                'payment_type' => '1',
                'image' => 'cod.png',
                'is_available' => 1,
                'is_activate' => 1,
                'reorder_id' => 1,
            ],
            [
                'vendor_id' => $vendorId,
                'payment_name' => 'RazorPay',
                'payment_type' => '2',
                'environment' => 'sandbox',
                'currency' => 'INR',
                'image' => 'razorpay.png',
                'is_available' => 2,
                'is_activate' => 1,
                'reorder_id' => 2,
            ],
            [
                'vendor_id' => $vendorId,
                'payment_name' => 'Stripe',
                'payment_type' => '3',
                'environment' => 'sandbox',
                'currency' => 'USD',
                'image' => 'stripe.png',
                'is_available' => 2,
                'is_activate' => 1,
                'reorder_id' => 3,
            ],
            [
                'vendor_id' => $vendorId,
                'payment_name' => 'Bank Transfer',
                'payment_type' => '6',
                'image' => 'bank.png',
                'payment_description' => 'Please transfer money to our bank account',
                'is_available' => 2,
                'is_activate' => 1,
                'reorder_id' => 4,
            ]
        ];

        foreach ($defaultPayments as $payment) {
            DB::table('payments')->insert(array_merge($payment, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer les paiements par défaut
        DB::table('payments')->truncate();
    }
}
