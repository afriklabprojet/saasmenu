<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'customer_id' => null,
            'user_id' => User::factory()->create(['type' => 2])->id,
            'type' => $this->faker->randomElement(['order', 'payment', 'system', 'promotion']),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph,
            'data' => [
                'order_id' => $this->faker->numberBetween(1, 100),
                'amount' => $this->faker->randomFloat(2, 10, 500),
            ],
            'read_at' => null,
            'sent_at' => now(),
            'action_url' => $this->faker->url,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
        ];
    }

    /**
     * Indicate that the notification is read
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification is unread
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Set notification for a specific user
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
            'customer_id' => null,
        ]);
    }

    /**
     * Set notification for a specific customer
     */
    public function forCustomer(int $customerId): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customerId,
            'user_id' => null,
        ]);
    }
}
