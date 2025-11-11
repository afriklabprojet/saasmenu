<?php

namespace Database\Factories;

use App\Models\RefundPrivacypolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundPrivacypolicyFactory extends Factory
{
    protected $model = RefundPrivacypolicy::class;

    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'refund_content' => $this->faker->paragraphs(4, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
