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
            
            // Customer info (nouvelles colonnes)
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->email(),
            'mobile' => $this->faker->phoneNumber(), // Nom correct de la colonne
            'address' => $this->faker->address(),
            
            // Anciennes colonnes user_* pour compatibilité
            'user_name' => $this->faker->name(),
            'user_email' => $this->faker->email(),
            'user_mobile' => $this->faker->phoneNumber(),
            
            'sub_total' => $subtotal,
            'tax' => $tax,
            'tax_amount' => $tax,
            'delivery_charge' => $deliveryCharge,
            'offer_amount' => $discount,
            'grand_total' => $grandTotal,
            'status' => $this->faker->numberBetween(1, 6), // 1=Pending, 2=Confirmed, etc.
            'status_type' => $this->faker->numberBetween(1, 4), // 1=Pending, 2=Accepted, 3=Delivered, 4=Cancelled
            'order_type' => $this->faker->numberBetween(1, 3), // 1=Delivery, 2=Pickup, 3=Dine-in
            'payment_status' => $this->faker->numberBetween(1, 3), // 1=Pending, 2=Paid, 3=Failed
            'payment_type' => $this->faker->numberBetween(1, 6), // 1=COD, 2-6=Online
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'paypal', 'stripe']),
            'order_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'delivery_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'delivery_time' => $this->faker->time(),
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
