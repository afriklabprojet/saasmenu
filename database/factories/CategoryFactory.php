<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = $this->faker->randomElement([
            'Appetizers', 'Main Course', 'Desserts', 'Beverages', 'Salads',
            'Soups', 'Pizza', 'Burgers', 'Pasta', 'Seafood', 'Vegetarian'
        ]);

        return [
            'vendor_id' => User::factory()->create(['type' => 2]),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'image' => 'category-default.jpg',
            'is_available' => 1,
            'is_deleted' => 2,
            'reorder_id' => 0,
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
