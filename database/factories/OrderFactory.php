<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 100);
        $deliveryCharge = $this->faker->randomFloat(2, 0, 10);
        $tax = $subtotal * 0.08; // 8% tax
        $discount = $this->faker->randomFloat(2, 0, 10);
        $grandTotal = $subtotal + $deliveryCharge + $tax - $discount;

        return [
            'order_number' => 'ORD-' . $this->faker->unique()->randomNumber(8),
            'user_id' => User::factory()->create(['type' => 3]), // Customer
            'vendor_id' => User::factory()->create(['type' => 2]), // Vendor

            // User info (colonnes existantes dans la migration)
            'user_name' => $this->faker->name(),
            'user_email' => $this->faker->email(),
            'user_mobile' => $this->faker->phoneNumber(),

            // Billing address
            'billing_address' => $this->faker->streetAddress(),
            'billing_landmark' => $this->faker->secondaryAddress(),
            'billing_postal_code' => $this->faker->postcode(),
            'billing_city' => $this->faker->city(),
            'billing_state' => $this->faker->state(),
            'billing_country' => $this->faker->country(),

            // Shipping address
            'shipping_address' => $this->faker->streetAddress(),
            'shipping_landmark' => $this->faker->secondaryAddress(),
            'shipping_postal_code' => $this->faker->postcode(),
            'shipping_city' => $this->faker->city(),
            'shipping_state' => $this->faker->state(),
            'shipping_country' => $this->faker->country(),

            'sub_total' => $subtotal,
            'offer_code' => $this->faker->optional()->word(),
            'offer_amount' => $discount,
            'tax_amount' => $tax,
            'shipping_area' => $this->faker->city(),
            'delivery_charge' => $deliveryCharge,
            'grand_total' => $grandTotal,

            'transaction_id' => $this->faker->optional()->uuid(),
            'transaction_type' => $this->faker->numberBetween(1, 6), // 1=COD, 2-6=Online

            'status' => $this->faker->numberBetween(1, 5), // 1=Placed, 2=Confirmed, 3=Cancelled by admin, 4=Cancelled by user, 5=Delivered

            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'payment_status' => 1,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
            'payment_status' => 2,
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 5,
            'payment_status' => 2,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 6,
        ]);
    }
}
