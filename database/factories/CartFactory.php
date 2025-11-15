<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'vendor_id' => User::factory()->create(['type' => 2])->id,
            'user_id' => User::factory()->create(['type' => 3])->id,
            'session_id' => null,
            'product_id' => Item::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_slug' => $this->faker->slug,
            'product_image' => 'product.jpg',
            'attribute' => null,
            'variation_id' => null,
            'variation_name' => null,
            'qty' => $this->faker->numberBetween(1, 5),
            'product_price' => $this->faker->randomFloat(2, 5, 50),
            'product_tax' => $this->faker->randomFloat(2, 0.5, 5),
        ];
    }
}
