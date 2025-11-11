<?php

namespace Database\Factories;

use App\Models\About;
use Illuminate\Database\Eloquent\Factories\Factory;

class AboutFactory extends Factory
{
    protected $model = About::class;

    public function definition(): array
    {
        return [
            'vendor_id' => 1,
            'about_content' => $this->faker->paragraphs(3, true),
            'image' => 'about-' . $this->faker->uuid() . '.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
