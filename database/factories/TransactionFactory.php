<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'transaction_id' => 'tx_' . $this->faker->unique()->alphaNumeric(16),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal', 'razorpay']),
            'gateway_response' => json_encode([
                'id' => $this->faker->alphaNumeric(20),
                'status' => 'succeeded'
            ]),
            'processing_fee' => $this->faker->randomFloat(2, 0.30, 15.00),
            'processed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => now()
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'processed_at' => now()
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'amount' => -abs($attributes['amount'])
        ]);
    }
}
