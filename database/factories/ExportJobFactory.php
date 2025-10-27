<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportJobFactory extends Factory
{
    protected $model = \App\Models\ExportJob::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']);
        $dataType = $this->faker->randomElement(['menus', 'customers', 'orders', 'categories']);
        $format = $this->faker->randomElement(['csv', 'excel', 'json']);

        return [
            'user_id' => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'type' => 'export',
            'data_type' => $dataType,
            'format' => $format,
            'file_name' => $dataType . '_export_' . $this->faker->date() . '.' . ($format === 'excel' ? 'xlsx' : $format),
            'file_path' => $status === 'completed' ? 'exports/' . $this->faker->uuid() . '.' . ($format === 'excel' ? 'xlsx' : $format) : null,
            'status' => $status,
            'total_records' => $this->faker->numberBetween(10, 1000),
            'exported_records' => $status === 'completed' ? function (array $attributes) {
                return $attributes['total_records'];
            } : $this->faker->numberBetween(0, 500),
            'filters' => [
                'date_from' => $this->faker->optional()->date(),
                'date_to' => $this->faker->optional()->date(),
                'category_id' => $this->faker->optional()->randomNumber(),
                'status' => $this->faker->optional()->randomElement(['active', 'inactive'])
            ],
            'settings' => [
                'include_headers' => true,
                'include_images' => $this->faker->boolean(),
                'date_format' => 'Y-m-d',
                'currency_format' => 'USD'
            ],
            'columns' => ['id', 'name', 'description', 'price', 'created_at'],
            'file_size' => $status === 'completed' ? $this->faker->numberBetween(1024, 1048576) : null, // bytes
            'download_url' => $status === 'completed' ? $this->faker->url() : null,
            'expires_at' => $status === 'completed' ? $this->faker->dateTimeBetween('now', '+7 days') : null,
            'started_at' => $status !== 'pending' ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'completed_at' => $status === 'completed' ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'exported_records' => 0,
            'file_path' => null,
            'file_size' => null,
            'download_url' => null,
            'expires_at' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        $format = $this->faker->randomElement(['csv', 'excel', 'json']);
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'format' => $format,
            'exported_records' => $attributes['total_records'],
            'file_path' => 'exports/' . $this->faker->uuid() . '.' . ($format === 'excel' ? 'xlsx' : $format),
            'file_size' => $this->faker->numberBetween(1024, 1048576),
            'download_url' => '/download/export/' . $this->faker->uuid(),
            'expires_at' => $this->faker->dateTimeBetween('now', '+7 days'),
            'started_at' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'completed_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'exported_records' => 0,
            'file_path' => null,
            'file_size' => null,
            'download_url' => null,
            'expires_at' => null,
            'started_at' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'completed_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    public function csv(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'csv',
            'file_name' => str_replace(['xlsx', 'json'], 'csv', $attributes['file_name']),
            'file_path' => str_replace(['xlsx', 'json'], 'csv', $attributes['file_path'] ?? ''),
        ]);
    }

    public function excel(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'excel',
            'file_name' => str_replace(['csv', 'json'], 'xlsx', $attributes['file_name']),
            'file_path' => str_replace(['csv', 'json'], 'xlsx', $attributes['file_path'] ?? ''),
        ]);
    }

    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'json',
            'file_name' => str_replace(['csv', 'xlsx'], 'json', $attributes['file_name']),
            'file_path' => str_replace(['csv', 'xlsx'], 'json', $attributes['file_path'] ?? ''),
        ]);
    }
}
