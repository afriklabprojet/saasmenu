<?php

namespace Database\Factories;

use App\Models\TableBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableBookFactory extends Factory
{
    protected $model = TableBook::class;

    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'mobile' => $this->faker->phoneNumber(),
            'guest_counts' => $this->faker->numberBetween(1, 10),
            'booking_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'booking_time' => $this->faker->randomElement(['12:00', '13:00', '14:00', '18:00', '19:00', '20:00', '21:00']),
            'message' => $this->faker->optional()->sentence(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
