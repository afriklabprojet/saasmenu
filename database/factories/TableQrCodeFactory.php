<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableQrCodeFactory extends Factory
{
    protected $model = \App\Models\TableQrCode::class;

    public function definition(): array
    {
        $isActive = $this->faker->boolean(90); // 90% chance of being active

        return [
            'restaurant_id' => Restaurant::factory(),
            'table_number' => $this->faker->unique()->numberBetween(1, 50),
            'table_name' => function (array $attributes) {
                return 'Table ' . $attributes['table_number'];
            },
            'qr_code' => $this->generateQrCode(),
            'url' => function (array $attributes) {
                return url('/menu/table/' . $attributes['qr_code']);
            },
            'capacity' => $this->faker->numberBetween(2, 8),
            'location' => $this->faker->randomElement([
                'Main Hall',
                'Terrace',
                'Private Room',
                'Garden',
                'Balcony',
                'VIP Section',
                'Bar Area'
            ]),
            'is_active' => $isActive,
            'scan_count' => $this->faker->numberBetween(0, 1000),
            'last_scanned_at' => $isActive ? $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now') : null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'scan_count' => $this->faker->numberBetween(10, 500),
            'last_scanned_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'scan_count' => $this->faker->numberBetween(0, 50),
            'last_scanned_at' => $this->faker->optional(0.5)->dateTimeBetween('-90 days', '-31 days'),
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'scan_count' => $this->faker->numberBetween(500, 2000),
            'last_scanned_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function vipTable(): static
    {
        return $this->state(fn (array $attributes) => [
            'table_name' => 'VIP Table ' . $attributes['table_number'],
            'location' => 'VIP Section',
            'capacity' => $this->faker->numberBetween(4, 10),
            'notes' => 'Premium seating with special service',
        ]);
    }

    public function terrace(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'Terrace',
            'notes' => 'Outdoor seating with city view',
        ]);
    }

    public function privateRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'table_name' => 'Private Room ' . $attributes['table_number'],
            'location' => 'Private Room',
            'capacity' => $this->faker->numberBetween(6, 12),
            'notes' => 'Private dining room for special occasions',
        ]);
    }

    public function withCustomNumber(int $tableNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'table_number' => $tableNumber,
            'table_name' => 'Table ' . $tableNumber,
            'qr_code' => $this->generateQrCode(),
        ]);
    }

    public function withLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }

    public function recentlyScanned(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_scanned_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'scan_count' => $this->faker->numberBetween(1, 10),
        ]);
    }

    private function generateQrCode(): string
    {
        // Generate a unique QR code identifier
        return strtoupper($this->faker->bothify('QR-####-????-####'));
    }
}
