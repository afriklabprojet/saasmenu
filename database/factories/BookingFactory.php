<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_number' => 'BK' . $this->faker->unique()->numberBetween(10000, 99999),
            'vendor_id' => User::factory()->create(['type' => 2])->id,
            'service_id' => $this->faker->numberBetween(1, 10),
            'service_image' => 'service.jpg',
            'service_name' => $this->faker->word . ' Service',
            'offer_code' => 'PROMO' . $this->faker->numberBetween(100, 999),
            'offer_amount' => 0,
            'booking_date' => $this->faker->date(),
            'booking_time' => $this->faker->time(),
            'address' => $this->faker->address,
            'payment_status' => $this->faker->randomElement([0, 1, 2]),
            'customer_name' => $this->faker->name,
            'mobile' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'message' => $this->faker->sentence,
            'sub_total' => $this->faker->randomFloat(2, 50, 200),
            'tax' => $this->faker->randomFloat(2, 5, 20),
            'grand_total' => $this->faker->randomFloat(2, 60, 250),
            'transaction_id' => 'TXN' . $this->faker->unique()->numerify('########'),
            'transaction_type' => $this->faker->randomElement(['card', 'cash', 'wallet']),
        ];
    }
}
