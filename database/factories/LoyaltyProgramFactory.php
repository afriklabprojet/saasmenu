<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyProgramFactory extends Factory
{
    protected $model = \App\Models\LoyaltyProgram::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['points', 'cashback', 'tiers']);

        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => $this->faker->randomElement([
                'Programme VIP',
                'Points Fidélité',
                'Club Premium',
                'Récompenses Gold'
            ]),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'points_per_euro' => $type === 'points' ? $this->faker->numberBetween(5, 20) : null,
            'point_value' => $type === 'points' ? $this->faker->randomFloat(3, 0.005, 0.02) : null,
            'cashback_percentage' => $type === 'cashback' ? $this->faker->randomFloat(2, 1, 10) : null,
            'minimum_spend' => $this->faker->optional()->randomFloat(2, 10, 100),
            'maximum_points_per_transaction' => $this->faker->optional()->numberBetween(100, 1000),
            'points_expiry_days' => $this->faker->optional()->numberBetween(365, 1095),
            'is_active' => $this->faker->boolean(80),
            'settings' => [
                'welcome_bonus' => $this->faker->optional()->numberBetween(50, 200),
                'birthday_bonus' => $this->faker->optional()->numberBetween(100, 500),
                'referral_bonus' => $this->faker->optional()->numberBetween(50, 300)
            ]
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function points(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'points',
            'points_per_euro' => $this->faker->numberBetween(5, 20),
            'point_value' => $this->faker->randomFloat(3, 0.005, 0.02),
            'cashback_percentage' => null,
        ]);
    }

    public function cashback(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cashback',
            'cashback_percentage' => $this->faker->randomFloat(2, 1, 10),
            'points_per_euro' => null,
            'point_value' => null,
        ]);
    }
}
