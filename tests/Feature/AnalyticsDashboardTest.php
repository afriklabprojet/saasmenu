<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a vendor user
        User::factory()->create([
            'id' => 1,
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'type' => 'vendor',
            'vendor_id' => 1
        ]);

        // Create products and categories
        DB::table('categories')->insert([
            ['id' => 1, 'vendor_id' => 1, 'name' => 'Main', 'slug' => 'main', 'created_at' => now(), 'updated_at' => now()]
        ]);

        DB::table('products')->insert([
            ['id' => 1, 'vendor_id' => 1, 'category_id' => 1, 'name' => 'Pizza', 'slug' => 'pizza', 'price' => 10.0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'vendor_id' => 1, 'category_id' => 1, 'name' => 'Pasta', 'slug' => 'pasta', 'price' => 8.5, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Create some orders for today
        $now = now();
        DB::table('orders')->insert([
            ['id' => 100, 'vendor_id' => 1, 'user_id' => 10, 'order_number' => 'ORD-100', 'user_name' => 'Alice', 'user_email' => 'alice@example.com', 'user_mobile' => '000', 'billing_address' => '', 'billing_landmark' => '', 'billing_postal_code' => '', 'billing_city' => '', 'billing_state' => '', 'billing_country' => '', 'shipping_address' => '', 'shipping_landmark' => '', 'shipping_postal_code' => '', 'shipping_city' => '', 'shipping_state' => '', 'shipping_country' => '', 'sub_total' => 18.5, 'offer_code' => null, 'offer_amount' => 0.0, 'tax_amount' => 0.0, 'shipping_area' => '', 'delivery_charge' => 0.0, 'grand_total' => 18.5, 'transaction_id' => null, 'transaction_type' => 1, 'status' => 5, 'notes' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 101, 'vendor_id' => 1, 'user_id' => 11, 'order_number' => 'ORD-101', 'user_name' => 'Bob', 'user_email' => 'bob@example.com', 'user_mobile' => '111', 'billing_address' => '', 'billing_landmark' => '', 'billing_postal_code' => '', 'billing_city' => '', 'billing_state' => '', 'billing_country' => '', 'shipping_address' => '', 'shipping_landmark' => '', 'shipping_postal_code' => '', 'shipping_city' => '', 'shipping_state' => '', 'shipping_country' => '', 'sub_total' => 10.0, 'offer_code' => null, 'offer_amount' => 0.0, 'tax_amount' => 0.0, 'shipping_area' => '', 'delivery_charge' => 0.0, 'grand_total' => 10.0, 'transaction_id' => null, 'transaction_type' => 1, 'status' => 3, 'notes' => null, 'created_at' => $now->copy()->subHours(2), 'updated_at' => $now->copy()->subHours(2)]
        ]);

        // Create order_details linking products
        DB::table('order_details')->insert([
            ['id' => 1000, 'vendor_id' => 1, 'user_id' => 10, 'session_id' => null, 'order_id' => 100, 'product_id' => 1, 'product_name' => 'Pizza', 'product_slug' => 'pizza', 'product_image' => null, 'attribute' => null, 'variation_id' => null, 'variation_name' => null, 'product_price' => 10.0, 'product_tax' => 0.0, 'qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 1001, 'vendor_id' => 1, 'user_id' => 10, 'session_id' => null, 'order_id' => 100, 'product_id' => 2, 'product_name' => 'Pasta', 'product_slug' => 'pasta', 'product_image' => null, 'attribute' => null, 'variation_id' => null, 'variation_name' => null, 'product_price' => 8.5, 'product_tax' => 0.0, 'qty' => 1, 'created_at' => $now, 'updated_at' => $now]
        ]);
    }

    public function test_dashboard_endpoint_returns_expected_structure()
    {
        $response = $this->getJson('/api/analytics/dashboard/1?period=today');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'revenue',
                'orders',
                'customers',
                'products',
                'trends',
                'insights',
                'generated_at',
                'period'
            ],
            'meta'
        ]);

        $json = $response->json('data');
        $this->assertIsArray($json['revenue']);
        $this->assertIsArray($json['orders']);
        $this->assertIsArray($json['products']);
    }

    public function test_widgets_endpoint_requires_auth()
    {
        $response = $this->getJson('/api/dashboard-widgets/');
        $response->assertStatus(401);
    }

    public function test_realtime_endpoint()
    {
        $response = $this->getJson('/api/analytics/realtime/1');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'realtime', 'hourly_breakdown']);
    }
}
