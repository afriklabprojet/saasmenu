<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CinetPayPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all vendor users
        $vendors = User::where('type', 1)->get();
        
        foreach ($vendors as $vendor) {
            // Check if CinetPay already exists for this vendor
            $existingPayment = DB::table('payments')
                ->where('vendor_id', $vendor->id)
                ->where('payment_type', 16)
                ->first();
                
            if (!$existingPayment) {
                // Insert CinetPay as the first payment method (priority 0)
                DB::table('payments')->insert([
                    'vendor_id' => $vendor->id,
                    'payment_name' => 'CinetPay',
                    'payment_type' => 16,
                    'environment' => 'sandbox',
                    'public_key' => '', // Will be configured by admin
                    'secret_key' => '', // Will be configured by admin
                    'currency' => 'XOF', // Default West African CFA Franc
                    'image' => 'cinetpay.png',
                    'is_available' => 1,
                    'is_activate' => 1,
                    'reorder_id' => 0, // First in order (highest priority)
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "CinetPay payment method added for vendor: {$vendor->name}\n";
            }
        }
        
        // Update existing payment methods to have lower priority
        DB::table('payments')
            ->where('payment_type', '!=', 16)
            ->where('reorder_id', '<', 1)
            ->update(['reorder_id' => DB::raw('reorder_id + 1')]);
        
        echo "CinetPay has been set as the default payment method for all vendors.\n";
    }
}