<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\POSTerminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class POSSessionFactory extends Factory
{
    protected $model = \App\Models\POSSession::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 week', 'now');
        $status = $this->faker->randomElement(['active', 'closed']);

        return [
            'restaurant_id' => Restaurant::factory(),
            'terminal_id' => POSTerminal::factory(),
            'user_id' => User::factory(),
            'status' => $status,
            'started_at' => $startedAt,
            'ended_at' => $status === 'closed' ? $this->faker->dateTimeBetween($startedAt, 'now') : null,
            'opening_cash' => $this->faker->randomFloat(2, 50, 200),
            'closing_cash' => $status === 'closed' ? $this->faker->randomFloat(2, 100, 500) : null,
            'total_sales' => $status === 'closed' ? $this->faker->randomFloat(2, 0, 1000) : 0,
            'total_transactions' => $status === 'closed' ? $this->faker->numberBetween(0, 50) : 0,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'ended_at' => null,
            'closing_cash' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'ended_at' => $this->faker->dateTimeBetween($attributes['started_at'], 'now'),
            'closing_cash' => $this->faker->randomFloat(2, 100, 500),
            'total_sales' => $this->faker->randomFloat(2, 0, 1000),
            'total_transactions' => $this->faker->numberBetween(1, 50),
        ]);
    }
}
