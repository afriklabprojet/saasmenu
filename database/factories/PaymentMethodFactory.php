<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Credit Card', 'PayPal', 'Stripe', 'Razorpay', 'Bank Transfer']),
            'type' => $this->faker->randomElement(['stripe', 'paypal', 'razorpay', 'bank']),
            'provider' => $this->faker->randomElement(['stripe', 'paypal', 'razorpay']),
            'is_active' => 1,
            'configuration' => json_encode([
                'api_key' => $this->faker->alphaNumeric(32),
                'secret_key' => $this->faker->alphaNumeric(64),
                'webhook_url' => $this->faker->url()
            ]),
            'processing_fee_type' => $this->faker->randomElement(['percentage', 'fixed']),
            'processing_fee_value' => $this->faker->randomFloat(2, 0.1, 5.0),
            'min_amount' => $this->faker->randomFloat(2, 1, 10),
            'max_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'supported_currencies' => json_encode(['USD', 'EUR', 'GBP']),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 1
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 0
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Stripe',
            'type' => 'stripe',
            'provider' => 'stripe'
        ]);
    }

    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PayPal',
            'type' => 'paypal',
            'provider' => 'paypal'
        ]);
    }
}
