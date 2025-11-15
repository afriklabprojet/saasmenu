<?php

namespace Database\Factories;

use App\Models\CustomStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomStatus>
 */
class CustomStatusFactory extends Factory
{
    protected $model = CustomStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'name' => $this->faker->randomElement([
                'Pending',
                'Accepted',
                'Preparing',
                'Ready',
                'Out for Delivery',
                'Delivered',
                'Cancelled',
            ]),
            'type' => $this->faker->numberBetween(1, 4), // 1=Pending, 2=Accepted, 3=Delivered, 4=Cancelled
            'order_type' => 1, // 1=Delivery, 2=Pickup, 3=Dine-in
            'is_available' => 1,
            'is_deleted' => 2, // 2=Not deleted
        ];
    }

    /**
     * Indicate that the status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Order Pending',
            'type' => 1,
        ]);
    }

    /**
     * Indicate that the status is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Order Accepted',
            'type' => 2,
        ]);
    }

    /**
     * Indicate that the status is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Order Delivered',
            'type' => 3,
        ]);
    }

    /**
     * Indicate that the status is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Order Cancelled',
            'type' => 4,
        ]);
    }
}
