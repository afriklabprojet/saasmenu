<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
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
            'cat_id' => Category::factory(),
            'item_name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['Pizza', 'Burger', 'Pasta', 'Salad', 'Sandwich']),
            'item_price' => $this->faker->randomFloat(2, 5, 50),
            'item_original_price' => function (array $attributes) {
                return $attributes['item_price'] + $this->faker->randomFloat(2, 0, 10);
            },
            'item_description' => $this->faker->paragraph(),
            'item_image' => $this->faker->imageUrl(400, 300, 'food'),
            'tax' => $this->faker->randomFloat(2, 0, 5),
            'has_variants' => $this->faker->boolean(30), // 30% chance of having variants
            'is_available' => 1,
            'slug' => $this->faker->slug(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the item is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => 0,
        ]);
    }

    /**
     * Indicate that the item has variants.
     */
    public function withVariants(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_variants' => 1,
        ]);
    }

    /**
     * Indicate that the item is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_price' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the item is cheap.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_price' => $this->faker->randomFloat(2, 1, 10),
        ]);
    }
}
