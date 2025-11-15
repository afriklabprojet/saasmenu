<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RestaurantApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_restaurants_list()
    {
        Restaurant::factory()->count(3)->create(['is_available' => 1]);

        $response = $this->getJson('/api/restaurants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'address',
                        'is_available'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_restaurant_details()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create([
            'vendor_id' => $vendor->id,
            'is_available' => 1
        ]);

        $response = $this->getJson("/api/restaurants/{$restaurant->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'address',
                    'phone',
                    'email'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_restaurant_menu()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);

        $category = Category::factory()->create(['vendor_id' => $vendor->id]);
        Item::factory()->count(5)->create([
            'vendor_id' => $vendor->id,
            'cat_id' => $category->id,
            'is_available' => 1
        ]);

        $response = $this->getJson("/api/restaurants/{$restaurant->id}/menu");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'category'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_vendor_endpoints()
    {
        $response = $this->postJson('/api/restaurants', [
            'name' => 'New Restaurant'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_vendor_can_create_restaurant()
    {
        $vendor = User::factory()->create(['type' => 2]);
        Sanctum::actingAs($vendor);

        $restaurantData = [
            'name' => 'New API Restaurant',
            'slug' => 'new-api-restaurant',
            'email' => 'api@restaurant.com',
            'mobile' => '9876543210',
            'address' => 'API Street 123',
            'city' => 'API City',
            'state' => 'API State',
            'postal_code' => '54321',
            'country' => 'API Country',
            'timezone' => 'UTC',
            'currency' => 'USD'
        ];

        $response = $this->postJson('/api/restaurants', $restaurantData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug'
                ]
            ]);

        $this->assertDatabaseHas('restaurants', [
            'name' => 'New API Restaurant',
            'vendor_id' => $vendor->id
        ]);
    }

    /** @test */
    public function vendor_can_update_their_restaurant()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);

        Sanctum::actingAs($vendor);

        $updateData = [
            'name' => 'Updated Restaurant Name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/restaurants/{$restaurant->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Updated Restaurant Name'
        ]);
    }

    /** @test */
    public function vendor_cannot_update_other_vendor_restaurant()
    {
        $vendor1 = User::factory()->create(['type' => 2]);
        $vendor2 = User::factory()->create(['type' => 2]);

        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor1->id]);

        Sanctum::actingAs($vendor2);

        $response = $this->putJson("/api/restaurants/{$restaurant->id}", [
            'name' => 'Unauthorized Update'
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_can_search_restaurants_by_name()
    {
        Restaurant::factory()->create(['name' => 'Pizza Palace', 'is_available' => 1]);
        Restaurant::factory()->create(['name' => 'Burger King', 'is_available' => 1]);
        Restaurant::factory()->create(['name' => 'Pizza Corner', 'is_available' => 1]);

        $response = $this->getJson('/api/restaurants?search=pizza');

        $response->assertStatus(200);

        $restaurants = $response->json('data');
        $this->assertCount(2, $restaurants);

        foreach ($restaurants as $restaurant) {
            $this->assertStringContainsStringIgnoringCase('pizza', $restaurant['name']);
        }
    }

    /** @test */
    public function it_can_filter_restaurants_by_city()
    {
        Restaurant::factory()->create(['city' => 'New York', 'is_available' => 1]);
        Restaurant::factory()->create(['city' => 'Los Angeles', 'is_available' => 1]);
        Restaurant::factory()->create(['city' => 'New York', 'is_available' => 1]);

        $response = $this->getJson('/api/restaurants?city=New York');

        $response->assertStatus(200);

        $restaurants = $response->json('data');
        $this->assertCount(2, $restaurants);
    }
}
