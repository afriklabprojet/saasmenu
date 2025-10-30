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
            'vendor_id' => User::factory(),
            'name' => $this->faker->company() . ' Restaurant',
            'slug' => $this->faker->slug(),
            'email' => $this->faker->companyEmail(),
            'mobile' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'description' => $this->faker->paragraph(),
            'timezone' => 'UTC',
            'currency' => 'USD',
            'is_available' => 1,
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
            'is_available' => 0,
        ]);
    }

    /**
     * Indicate that the restaurant is in a specific city.
     */
    public function inCity(string $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $city,
        ]);
    }
}
