<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportJobFactory extends Factory
{
    protected $model = \App\Models\ImportJob::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']);
        $dataType = $this->faker->randomElement(['menus', 'customers', 'orders', 'categories']);

        return [
            'user_id' => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'type' => 'import',
            'data_type' => $dataType,
            'file_name' => $dataType . '_import_' . $this->faker->date() . '.csv',
            'file_path' => 'imports/' . $this->faker->uuid() . '.csv',
            'status' => $status,
            'total_rows' => $this->faker->numberBetween(10, 1000),
            'processed_rows' => $status === 'completed' ? function (array $attributes) {
                return $attributes['total_rows'];
            } : $this->faker->numberBetween(0, 500),
            'successful_rows' => $status === 'completed' ? function (array $attributes) {
                return $this->faker->numberBetween(
                    (int)($attributes['total_rows'] * 0.8),
                    $attributes['total_rows']
                );
            } : 0,
            'failed_rows' => function (array $attributes) {
                return $attributes['processed_rows'] - $attributes['successful_rows'];
            },
            'mapping' => [
                'name' => 'name',
                'description' => 'description',
                'price' => 'price'
            ],
            'settings' => [
                'skip_first_row' => true,
                'update_existing' => false,
                'batch_size' => 100
            ],
            'errors' => $status === 'failed' ? [
                ['row' => 5, 'error' => 'Invalid email format'],
                ['row' => 12, 'error' => 'Required field missing']
            ] : [],
            'started_at' => $status !== 'pending' ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'completed_at' => $status === 'completed' ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_rows' => $attributes['total_rows'],
            'successful_rows' => $this->faker->numberBetween(
                (int)($attributes['total_rows'] * 0.8),
                $attributes['total_rows']
            ),
            'started_at' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'completed_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'errors' => [
                ['row' => 1, 'error' => 'File format not supported'],
                ['row' => 5, 'error' => 'Invalid data type']
            ],
            'started_at' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'completed_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }
}
