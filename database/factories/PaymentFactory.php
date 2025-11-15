<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'vendor_id' => User::factory()->create(['type' => 2])->id,
            'payment_name' => $this->faker->randomElement(['Stripe', 'PayPal', 'Cash', 'Card']),
            'payment_type' => $this->faker->randomElement(['online', 'cash', 'card']),
            'environment' => $this->faker->randomElement(['sandbox', 'production']),
            'public_key' => $this->faker->uuid,
            'secret_key' => $this->faker->uuid,
            'currency' => $this->faker->currencyCode,
            'image' => 'payment.jpg',
            'payment_description' => $this->faker->sentence,
            'is_available' => 1,
        ];
    }
}
