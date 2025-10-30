<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => User::factory()->create(['type' => 2]),
            'category_name' => $this->faker->randomElement([
                'Appetizers', 'Main Course', 'Desserts', 'Beverages', 'Salads',
                'Soups', 'Pizza', 'Burgers', 'Pasta', 'Seafood', 'Vegetarian'
            ]),
            'category_image' => $this->faker->imageUrl(300, 200, 'food'),
            'is_available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the category is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => 0,
        ]);
    }
}
