<?php

namespace Database\Factories;

use App\Models\Terms;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermsFactory extends Factory
{
    protected $model = Terms::class;

    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'terms_content' => $this->faker->paragraphs(5, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
