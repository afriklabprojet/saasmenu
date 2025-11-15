<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['type' => 2]), // Vendor user
            'restaurant_name' => $this->faker->company() . ' Restaurant',
            'restaurant_slug' => $this->faker->unique()->slug(),
            'restaurant_address' => $this->faker->streetAddress(),
            'restaurant_phone' => $this->faker->phoneNumber(),
            'restaurant_email' => $this->faker->companyEmail(),
            'restaurant_image' => $this->faker->imageUrl(400, 300, 'food'),
            'description' => $this->faker->paragraph(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'is_active' => 1,
            'delivery_fee' => $this->faker->randomFloat(2, 0, 10),
            'minimum_order' => $this->faker->randomFloat(2, 0, 20),
            'delivery_time' => $this->faker->numberBetween(15, 60),
            'opening_time' => '09:00',
            'closing_time' => '22:00',
            'is_open' => 1,
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the restaurant is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 0,
        ]);
    }

    /**
     * Indicate that the restaurant is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => 0,
        ]);
    }
}
