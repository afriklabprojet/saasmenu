<?php

namespace Database\Factories;

use App\Models\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingsFactory extends Factory
{
    protected $model = Settings::class;

    public function definition(): array
    {
        $key = $this->faker->randomElement([
            'app_name',
            'app_description',
            'currency',
            'timezone',
            'default_language',
            'maintenance_mode',
            'email_verification',
            'sms_verification',
            'tax_rate',
            'delivery_fee'
        ]);

        $value = match($key) {
            'app_name' => 'RestroSaaS',
            'app_description' => 'Multi-Restaurant Management System',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'default_language' => 'en',
            'maintenance_mode' => '0',
            'email_verification' => '1',
            'sms_verification' => '0',
            'tax_rate' => '10.00',
            'delivery_fee' => '5.00',
            default => $this->faker->word()
        };

        return [
            'key' => $key,
            'value' => $value,
            'type' => $this->faker->randomElement(['string', 'integer', 'boolean', 'json']),
            'group' => $this->faker->randomElement(['general', 'payment', 'notification', 'security']),
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(30), // 30% chance of being public
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function appName(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'app_name',
            'value' => 'RestroSaaS Test',
            'type' => 'string',
            'group' => 'general'
        ]);
    }

    public function currency(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'currency',
            'value' => 'USD',
            'type' => 'string',
            'group' => 'general'
        ]);
    }

    public function taxRate(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'tax_rate',
            'value' => '10.00',
            'type' => 'string',
            'group' => 'payment'
        ]);
    }

    public function publicSetting(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true
        ]);
    }

    public function privateSetting(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false
        ]);
    }
}
