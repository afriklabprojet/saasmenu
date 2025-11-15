<?php

namespace Database\Factories;

use App\Models\Promocode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromocodeFactory extends Factory
{
    protected $model = Promocode::class;

    public function definition(): array
    {
        $startDate = now();
        $endDate = now()->addDays(30);

        return [
            'vendor_id' => User::factory()->create(['type' => 2])->id,
            'offer_name' => $this->faker->word . ' Offer',
            'offer_code' => strtoupper($this->faker->bothify('???###')),
            'offer_amount' => $this->faker->randomFloat(2, 5, 50),
            'offer_type' => $this->faker->randomElement([1, 2]),
            'min_amount' => $this->faker->numberBetween(0, 50),
            'usage_type' => 1,
            'usage_limit' => $this->faker->numberBetween(10, 100),
            'start_date' => $startDate,
            'exp_date' => $endDate,
        ];
    }
}
