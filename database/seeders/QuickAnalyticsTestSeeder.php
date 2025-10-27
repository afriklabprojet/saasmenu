<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuickAnalyticsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Configuration rapide pour tester Analytics...');

        // Get or create vendor
        $vendor = DB::table('users')->where('type', 2)->first();

        if (!$vendor) {
            $this->command->info('📝 Création d\'un vendor de test...');
            $vendor_id = DB::table('users')->insertGetId([
                'name' => 'Restaurant Test',
                'email' => 'vendor@test.com',
                'mobile' => '+221771111111',
                'password' => bcrypt('password'),
                'type' => 2,
                'is_available' => 1,
                'is_verified' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $vendor_id = $vendor->id;
        }

        // Get or create customer
        $customer = DB::table('users')->where('type', 3)->first();

        if (!$customer) {
            $this->command->info('📝 Création d\'un client de test...');
            $customer_id = DB::table('users')->insertGetId([
                'name' => 'Client Test',
                'email' => 'customer@test.com',
                'mobile' => '+221772222222',
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

        // Get or create category
        $category = DB::table('categories')->where('vendor_id', $vendor_id)->first();

        if (!$category) {
            $this->command->info('📝 Création de catégories...');

            $categories = [
                ['name' => 'Plats Principaux', 'icon' => '🍽️'],
                ['name' => 'Boissons', 'icon' => '🥤'],
                ['name' => 'Desserts', 'icon' => '🍰'],
            ];

            $cat_ids = [];
            foreach ($categories as $cat) {
                $cat_ids[] = DB::table('categories')->insertGetId([
                    'name' => $cat['name'],
                    'vendor_id' => $vendor_id,
                    'is_available' => 1,
                    'is_deleted' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            $cat_ids = DB::table('categories')
                ->where('vendor_id', $vendor_id)
                ->pluck('id')
                ->toArray();
        }

        // Create items if not exist
        $itemCount = DB::table('items')->where('vendor_id', $vendor_id)->count();

        if ($itemCount < 10) {
            $this->command->info('📝 Création de produits...');

            $items = [
                ['Thiéboudienne', 3500, $cat_ids[0] ?? $cat_ids[0]],
                ['Yassa Poulet', 2500, $cat_ids[0] ?? $cat_ids[0]],
                ['Mafé', 3000, $cat_ids[0] ?? $cat_ids[0]],
                ['Domoda', 2800, $cat_ids[0] ?? $cat_ids[0]],
                ['Coca Cola', 500, $cat_ids[1] ?? $cat_ids[0]],
                ['Jus Bissap', 1000, $cat_ids[1] ?? $cat_ids[0]],
                ['Eau Minérale', 300, $cat_ids[1] ?? $cat_ids[0]],
                ['Thiakry', 1500, $cat_ids[2] ?? $cat_ids[0]],
                ['Sombi', 1200, $cat_ids[2] ?? $cat_ids[0]],
                ['Salade de Fruits', 2000, $cat_ids[2] ?? $cat_ids[0]],
            ];

            foreach ($items as $item) {
                DB::table('items')->insert([
                    'name' => $item[0],
                    'cat_id' => $item[2],
                    'vendor_id' => $vendor_id,
                    'price' => $item[1],
                    'is_available' => 1,
                    'is_deleted' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Get items
        $items = DB::table('items')->where('vendor_id', $vendor_id)->get();

        $this->command->info("\n✓ Vendor ID: {$vendor_id}");
        $this->command->info("✓ Customer ID: {$customer_id}");
        $this->command->info("✓ Produits: " . $items->count());

        // Generate orders
        $ordersGenerated = 0;

        $this->command->info("\n📅 Génération de commandes...");

        // Today - 15 orders
        for ($i = 0; $i < 15; $i++) {
            $this->createOrder($vendor_id, $customer_id, $items, Carbon::today());
            $ordersGenerated++;
        }

        // Yesterday - 12 orders
        for ($i = 0; $i < 12; $i++) {
            $this->createOrder($vendor_id, $customer_id, $items, Carbon::yesterday());
            $ordersGenerated++;
        }

        // This week - 30 orders
        for ($i = 0; $i < 30; $i++) {
            $randomDay = Carbon::now()->subDays(rand(2, 6));
            $this->createOrder($vendor_id, $customer_id, $items, $randomDay);
            $ordersGenerated++;
        }

        // This month - 50 orders
        for ($i = 0; $i < 50; $i++) {
            $randomDay = Carbon::now()->subDays(rand(7, 29));
            $this->createOrder($vendor_id, $customer_id, $items, $randomDay);
            $ordersGenerated++;
        }

        $this->command->info("\n✅ {$ordersGenerated} commandes créées !");
        $this->command->info("🎯 Dashboard analytics prêt à tester !");
        $this->command->info("\n📊 Accédez à: http://127.0.0.1:8000/admin/analytics/dashboard");
    }

    private function createOrder($vendor_id, $customer_id, $items, Carbon $date)
    {
        $hour = rand(8, 22);
        $minute = rand(0, 59);
        $orderDate = $date->copy()->setHour($hour)->setMinute($minute);

        $numItems = rand(1, 4);
        $randomItems = $items->random(min($numItems, $items->count()));

        $subtotal = 0;
        $orderItems = [];

        foreach ($randomItems as $item) {
            $quantity = rand(1, 3);
            $price = $item->price ?? 1000;
            $itemTotal = $price * $quantity;
            $subtotal += $itemTotal;

            $orderItems[] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'cat_id' => $item->cat_id,
                'quantity' => $quantity,
                'price' => $price,
                'item_total' => $itemTotal,
            ];
        }

        $tax = $subtotal * 0.18;
        $grand_total = $subtotal + $tax;

        $statusWeights = [1 => 10, 2 => 70, 3 => 20];
        $status = $this->weightedRandom($statusWeights);

        $order_id = DB::table('orders')->insertGetId([
            'order_number' => 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'vendor_id' => $vendor_id,
            'user_id' => $customer_id,
            'status' => $status,
            'payment_method' => 'cash',
            'payment_status' => $status == 2 ? 'paid' : 'pending',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $grand_total,
            'delivery_type' => rand(1, 3),
            'notes' => 'Test order',
            'created_at' => $orderDate,
            'updated_at' => $orderDate,
        ]);

        foreach ($orderItems as $orderItem) {
            DB::table('order_details')->insert([
                'order_id' => $order_id,
                'vendor_id' => $vendor_id,
                'product_id' => $orderItem['item_id'],
                'product_name' => $orderItem['item_name'],
                'product_price' => $orderItem['price'],
                'qty' => $orderItem['quantity'],
                'product_tax' => $orderItem['item_total'] * 0.18,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }
    }

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
