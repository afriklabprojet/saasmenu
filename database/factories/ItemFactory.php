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
        $price = $this->faker->randomFloat(2, 5, 50);
        return [
            'vendor_id' => User::factory()->create(['type' => 2]),
            'cat_id' => Category::factory(),
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['Pizza', 'Burger', 'Pasta', 'Salad', 'Sandwich']),
            'price' => $price,
            'original_price' => $price + $this->faker->randomFloat(2, 0, 10),
            'description' => $this->faker->paragraph(),
            'image' => $this->faker->imageUrl(400, 300, 'food'),
            'is_available' => 1,
            'is_deleted' => 0,
            'slug' => $this->faker->slug(),
            'stock_management' => 0,
            'qty' => 0,
            'min_order' => 1,
            'max_order' => 0,
            'low_qty' => 0,
            'tax' => null,
            'sku' => $this->faker->unique()->ean8(),
            'reorder_id' => 0,
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
            'price' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Indicate that the item is cheap.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 1, 10),
        ]);
    }
}
