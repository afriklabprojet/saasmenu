<?php

namespace Tests\Unit\Models;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Order;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_restaurant()
    {
        $vendor = User::factory()->create(['type' => 2]);

        $restaurantData = [
            'vendor_id' => $vendor->id,
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'email' => 'restaurant@example.com',
            'mobile' => '1234567890',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'is_available' => 1,
        ];

        $restaurant = Restaurant::create($restaurantData);

        $this->assertInstanceOf(Restaurant::class, $restaurant);
        $this->assertEquals($restaurantData['name'], $restaurant->name);
        $this->assertEquals($vendor->id, $restaurant->vendor_id);
        $this->assertDatabaseHas('restaurants', ['slug' => 'test-restaurant']);
    }

    /** @test */
    public function restaurant_belongs_to_vendor()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);

        $this->assertInstanceOf(User::class, $restaurant->vendor);
        $this->assertEquals($vendor->id, $restaurant->vendor->id);
    }

    /** @test */
    public function restaurant_can_have_orders()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);
        $customer = User::factory()->create(['type' => 3]);

        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id,
        ]);

        $this->assertTrue($restaurant->orders()->exists());
    }

    /** @test */
    public function restaurant_can_have_menu_items()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);

        $item = Item::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $this->assertTrue($restaurant->items()->exists());
    }

    /** @test */
    public function restaurant_slug_must_be_unique()
    {
        Restaurant::factory()->create(['slug' => 'unique-restaurant']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Restaurant::factory()->create(['slug' => 'unique-restaurant']);
    }

    /** @test */
    public function restaurant_can_be_enabled_or_disabled()
    {
        $restaurant = Restaurant::factory()->create(['is_available' => 1]);

        $this->assertTrue($restaurant->isAvailable());

        $restaurant->update(['is_available' => 0]);

        $this->assertFalse($restaurant->fresh()->isAvailable());
    }
}
