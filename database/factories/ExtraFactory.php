<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Extra>
 */
class ExtraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => \App\Models\Item::factory(),
            'name' => $this->faker->randomElement(['Extra Cheese', 'Extra Sauce', 'Extra Bacon', 'Extra Mushrooms', 'Extra Onions']),
            'price' => $this->faker->randomFloat(2, 0.50, 5.00),
            'is_available' => 1,
            'reorder_id' => 0,
        ];
    }
}
