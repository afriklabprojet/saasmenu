<?php

namespace Tests\Feature\Performance;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_loads_within_acceptable_time()
    {
        $admin = User::factory()->create(['type' => 1]);

        // Create some data
        User::factory()->count(50)->create(['type' => 2]); // Vendors
        Restaurant::factory()->count(100)->create();
        Order::factory()->count(200)->create();

        $startTime = microtime(true);

        $response = $this->actingAs($admin)
            ->get('/admin');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Dashboard should load within 2 seconds');
    }

    /** @test */
    public function api_restaurant_list_handles_large_dataset()
    {
        // Create a large number of restaurants
        Restaurant::factory()->count(1000)->create(['is_available' => 1]);

        $startTime = microtime(true);

        $response = $this->getJson('/api/restaurants?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $executionTime, 'API should respond within 1 second');

        $data = $response->json('data');
        $this->assertCount(50, $data);
    }

    /** @test */
    public function search_functionality_is_optimized()
    {
        // Create restaurants with searchable data
        Restaurant::factory()->count(500)->create(['is_available' => 1]);

        $startTime = microtime(true);

        $response = $this->getJson('/api/restaurants?search=pizza');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $executionTime, 'Search should complete within 500ms');
    }

    /** @test */
    public function order_creation_handles_concurrent_requests()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);

        $this->actingAs($customer);

        $orderData = [
            'vendor_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_mobile' => '1234567890',
            'grand_total' => 25.99,
            'status' => 1,
        ];

        $startTime = microtime(true);

        // Simulate multiple concurrent order creations
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $orderData['order_number'] = 'ORD-' . time() . '-' . $i;
            $responses[] = $this->post('/orders', $orderData);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        foreach ($responses as $response) {
            $response->assertRedirect();
        }

        $this->assertLessThan(3.0, $executionTime, 'Multiple orders should be created within 3 seconds');
        $this->assertEquals(5, Order::where('user_id', $customer->id)->count());
    }

    /** @test */
    public function memory_usage_is_within_limits()
    {
        $memoryBefore = memory_get_usage(true);

        // Create and load a substantial amount of data
        $restaurants = Restaurant::factory()->count(100)->create();
        $items = Item::factory()->count(500)->create();
        $orders = Order::factory()->count(200)->create();

        // Access the data
        foreach ($restaurants as $restaurant) {
            $restaurant->items;
            $restaurant->orders;
        }

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should not exceed 64MB for this operation
        $this->assertLessThan(64 * 1024 * 1024, $memoryUsed, 'Memory usage should be under 64MB');
    }
}
