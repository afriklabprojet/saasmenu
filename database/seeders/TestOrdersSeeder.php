<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Génération de commandes de test pour Analytics...');

        // Get first vendor
        $vendor = DB::table('users')->where('type', 2)->first();

        if (!$vendor) {
            $this->command->error('❌ Aucun vendor trouvé. Créez d\'abord un vendor.');
            return;
        }

        $vendor_id = $vendor->id;

        // Get some items
        $items = DB::table('items')
            ->where('vendor_id', $vendor_id)
            ->limit(10)
            ->get();

        if ($items->isEmpty()) {
            $this->command->error('❌ Aucun produit trouvé. Créez d\'abord des produits.');
            return;
        }

        // Get or create a test customer
        $customer = DB::table('users')->where('type', 3)->first();

        if (!$customer) {
            // Create test customer
            $customer_id = DB::table('users')->insertGetId([
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'mobile' => '+221771234567',
                'password' => bcrypt('password'),
                'type' => 3,
                'is_available' => 1,
                'is_verified' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customer_id = $customer->id;
        }

        $this->command->info("✓ Vendor ID: {$vendor_id}");
        $this->command->info("✓ Customer ID: {$customer_id}");
        $this->command->info("✓ Nombre de produits: " . $items->count());

        // Generate orders for different periods
        $ordersGenerated = 0;

        // Today - 15 orders
        $this->command->info("\n📅 Génération commandes aujourd'hui...");
        for ($i = 0; $i < 15; $i++) {
            $this->createOrder($vendor_id, $customer_id, $items, Carbon::today());
            $ordersGenerated++;
        }

        // Yesterday - 12 orders
        $this->command->info("📅 Génération commandes hier...");
        for ($i = 0; $i < 12; $i++) {
            $this->createOrder($vendor_id, $customer_id, $items, Carbon::yesterday());
            $ordersGenerated++;
        }

        // This week - 30 orders
        $this->command->info("📅 Génération commandes cette semaine...");
        for ($i = 0; $i < 30; $i++) {
            $randomDay = Carbon::now()->subDays(rand(2, 6));
            $this->createOrder($vendor_id, $customer_id, $items, $randomDay);
            $ordersGenerated++;
        }

        // This month - 50 orders
        $this->command->info("📅 Génération commandes ce mois...");
        for ($i = 0; $i < 50; $i++) {
            $randomDay = Carbon::now()->subDays(rand(7, 29));
            $this->createOrder($vendor_id, $customer_id, $items, $randomDay);
            $ordersGenerated++;
        }

        $this->command->info("\n✅ {$ordersGenerated} commandes de test créées avec succès !");
        $this->command->info("🎯 Vous pouvez maintenant tester le dashboard analytics !");
    }

    /**
     * Create a single order with random items
     */
    private function createOrder($vendor_id, $customer_id, $items, Carbon $date)
    {
        // Random hour between 8h and 22h
        $hour = rand(8, 22);
        $minute = rand(0, 59);
        $orderDate = $date->copy()->setHour($hour)->setMinute($minute);

        // Random number of items (1-4)
        $numItems = rand(1, 4);
        $randomItems = $items->random(min($numItems, $items->count()));

        // Calculate total
        $subtotal = 0;
        $orderItems = [];

        foreach ($randomItems as $item) {
            $quantity = rand(1, 3);
            $price = $item->price ?? rand(1000, 5000);
            $itemTotal = $price * $quantity;
            $subtotal += $itemTotal;

            $orderItems[] = [
                'item_id' => $item->id,
                'item_name' => $item->item_name ?? 'Test Item',
                'quantity' => $quantity,
                'price' => $price,
                'item_total' => $itemTotal,
            ];
        }

        // Add tax (18%)
        $tax = $subtotal * 0.18;
        $grand_total = $subtotal + $tax;

        // Random order status (2=accepted, 3=delivered, 4=cancelled)
        $statusWeights = [2 => 10, 3 => 70, 4 => 20]; // 70% delivered
        $status = $this->weightedRandom($statusWeights);

        // Insert order
        $order_id = DB::table('orders')->insertGetId([
            'order_number' => 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'vendor_id' => $vendor_id,
            'user_id' => $customer_id,
            'order_type' => rand(1, 3), // 1=delivery, 2=pickup, 3=table
            'status' => $status,
            'payment_type' => rand(1, 3), // COD, Card, Mobile
            'payment_status' => $status == 3 ? 2 : 1, // Paid if delivered
            'sub_total' => $subtotal,
            'tax' => $tax,
            'grand_total' => $grand_total,
            'status_type' => $status,
            'notes' => 'Commande de test générée automatiquement',
            'created_at' => $orderDate,
            'updated_at' => $orderDate,
        ]);

        // Insert order details
        foreach ($orderItems as $orderItem) {
            DB::table('order_details')->insert([
                'order_id' => $order_id,
                'item_id' => $orderItem['item_id'],
                'item_name' => $orderItem['item_name'],
                'price' => $orderItem['price'],
                'qty' => $orderItem['quantity'],
                'tax' => $orderItem['item_total'] * 0.18,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }
    }

    /**
     * Get weighted random value
     */
    private function weightedRandom($weights)
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        $current = 0;
        foreach ($weights as $value => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $value;
            }
        }

        return array_key_first($weights);
    }
}
