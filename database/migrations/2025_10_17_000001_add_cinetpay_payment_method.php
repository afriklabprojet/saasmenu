<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Payment;
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
        // Insert CinetPay payment method for all existing vendors
        $vendors = DB::table('users')->where('type', 1)->get();
        
        foreach ($vendors as $vendor) {
            // Check if CinetPay already exists for this vendor
            $existingPayment = DB::table('payments')
                ->where('vendor_id', $vendor->id)
                ->where('payment_type', 16)
                ->first();
                
            if (!$existingPayment) {
                DB::table('payments')->insert([
                    'vendor_id' => $vendor->id,
                    'payment_name' => 'CinetPay',
                    'payment_type' => 16,
                    'environment' => 'sandbox',
                    'public_key' => '',
                    'secret_key' => '',
                    'currency' => 'XOF',
                    'image' => 'cinetpay.png',
                    'is_available' => 1,
                    'is_activate' => 1,
                    'reorder_id' => 0, // Priority 0 to make it default
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update reorder_id for other payment methods to give CinetPay priority
        DB::table('payments')
            ->where('payment_type', '!=', 16)
            ->increment('reorder_id', 1);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove CinetPay payment method
        DB::table('payments')->where('payment_type', 16)->delete();
        
        // Restore original reorder_id for other payment methods
        DB::table('payments')
            ->where('reorder_id', '>', 0)
            ->decrement('reorder_id', 1);
    }
};