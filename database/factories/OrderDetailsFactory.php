<?php

namespace Database\Factories;

use App\Models\OrderDetails;
use App\Models\Order;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDetails>
 */
class OrderDetailsFactory extends Factory
{
    protected $model = OrderDetails::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 5, 50);

        return [
            'order_id' => Order::factory(),
            'product_id' => 1, // Use product_id instead of item_id
            'item_name' => $this->faker->words(3, true),
            'item_image' => $this->faker->imageUrl(400, 400, 'food'),
            'item_price' => $price,
            'tax' => $price * 0.1, // 10% tax
            'qty' => $quantity,
            'price' => $price * $quantity,
            'variants_id' => null,
            'variants_name' => null,
            'variants_price' => null,
            'extras_id' => null,
            'extras_name' => null,
            'extras_price' => null,
        ];
    }

    /**
     * Indicate that the order detail has variants.
     */
    public function withVariants(): static
    {
        return $this->state(fn (array $attributes) => [
            'variants_id' => $this->faker->numberBetween(1, 100),
            'variants_name' => $this->faker->randomElement(['Small', 'Medium', 'Large']),
            'variants_price' => $this->faker->randomFloat(2, 1, 10),
        ]);
    }

    /**
     * Indicate that the order detail has extras.
     */
    public function withExtras(): static
    {
        return $this->state(fn (array $attributes) => [
            'extras_id' => $this->faker->numberBetween(1, 100),
            'extras_name' => $this->faker->randomElement(['Extra Cheese', 'Extra Sauce', 'Extra Toppings']),
            'extras_price' => $this->faker->randomFloat(2, 0.5, 5),
        ]);
    }
}
