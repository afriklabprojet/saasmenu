<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class POSTerminalFactory extends Factory
{
    protected $model = \App\Models\POSTerminal::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => 'Terminal ' . $this->faker->randomNumber(2),
            'code' => 'POS-' . $this->faker->unique()->randomNumber(4),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance']),
            'location' => $this->faker->randomElement(['Caisse principale', 'Caisse bar', 'Caisse terrasse']),
            'hardware_id' => $this->faker->uuid(),
            'last_activity' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'settings' => [
                'receipt_printer' => true,
                'cash_drawer' => true,
                'barcode_scanner' => false
            ]
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
