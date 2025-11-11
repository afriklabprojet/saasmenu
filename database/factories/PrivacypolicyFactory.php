<?php

namespace Database\Factories;

use App\Models\Privacypolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivacypolicyFactory extends Factory
{
    protected $model = Privacypolicy::class;

    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'privacy_content' => $this->faker->paragraphs(5, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
