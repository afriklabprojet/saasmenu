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
            'user_id' => $vendor->id,
            'restaurant_name' => 'Test Restaurant',
            'restaurant_slug' => 'test-restaurant',
            'restaurant_email' => 'restaurant@example.com',
            'restaurant_phone' => '1234567890',
            'restaurant_address' => '123 Test Street',
            'is_active' => 1,
        ];

        $restaurant = Restaurant::create($restaurantData);

        $this->assertInstanceOf(Restaurant::class, $restaurant);
        $this->assertEquals($restaurantData['restaurant_name'], $restaurant->restaurant_name);
        $this->assertEquals($vendor->id, $restaurant->user_id);
        $this->assertDatabaseHas('restaurants', ['restaurant_slug' => 'test-restaurant']);
    }

    /** @test */
    public function restaurant_belongs_to_vendor()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['user_id' => $vendor->id]);

        $this->assertInstanceOf(User::class, $restaurant->vendor);
        $this->assertEquals($vendor->id, $restaurant->vendor->id);
    }

    /** @test */
    public function restaurant_can_have_orders()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['user_id' => $vendor->id]);
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
        $restaurant = Restaurant::factory()->create(['user_id' => $vendor->id]);

        $item = Item::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $this->assertTrue($restaurant->items()->exists());
    }

    /** @test */
    public function restaurant_slug_must_be_unique()
    {
        Restaurant::factory()->create(['restaurant_slug' => 'unique-restaurant']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Restaurant::factory()->create(['restaurant_slug' => 'unique-restaurant']);
    }

    /** @test */
    public function restaurant_can_be_enabled_or_disabled()
    {
        $restaurant = Restaurant::factory()->create(['is_active' => 1]);

        $this->assertTrue($restaurant->isActive());

        $restaurant->update(['is_active' => 0]);

        $this->assertFalse($restaurant->fresh()->isActive());
    }
}
