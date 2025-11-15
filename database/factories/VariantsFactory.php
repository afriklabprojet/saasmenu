<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variants>
 */
class VariantsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 15);
        return [
            'item_id' => \App\Models\Item::factory(),
            'name' => $this->faker->randomElement(['Small', 'Medium', 'Large', 'Extra Large']),
            'price' => $price,
            'original_price' => $price + $this->faker->randomFloat(2, 1, 5),
            'qty' => $this->faker->numberBetween(0, 100),
            'min_order' => 1,
            'max_order' => 10,
            'is_available' => 1,
            'stock_management' => 0,
        ];
    }
}
