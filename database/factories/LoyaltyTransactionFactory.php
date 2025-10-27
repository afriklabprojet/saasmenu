<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\LoyaltyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyTransactionFactory extends Factory
{
    protected $model = \App\Models\LoyaltyTransaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit', 'debit']);
        
        return [
            'member_id' => \App\Models\LoyaltyMember::factory(),
            'program_id' => LoyaltyProgram::factory(),
            'type' => $type,
            'points' => $this->faker->numberBetween(10, 500),
            'reason' => $type === 'credit' 
                ? $this->faker->randomElement([
                    'Achat en magasin',
                    'Bonus de bienvenue',
                    'Parrainage',
                    'Bonus anniversaire',
                    'Promotion spéciale'
                ])
                : $this->faker->randomElement([
                    'Réduction appliquée',
                    'Cadeau échangé',
                    'Remise fidélité',
                    'Offre spéciale'
                ]),
            'reference_type' => $this->faker->optional()->randomElement(['order', 'promotion', 'referral']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
            'balance_before' => $this->faker->numberBetween(0, 1000),
            'balance_after' => function (array $attributes) {
                return $attributes['type'] === 'credit' 
                    ? $attributes['balance_before'] + $attributes['points']
                    : max(0, $attributes['balance_before'] - $attributes['points']);
            },
            'processed_by' => User::factory(),
        ];
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'reason' => $this->faker->randomElement([
                'Achat en magasin',
                'Bonus de bienvenue',
                'Parrainage'
            ]),
        ]);
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'debit',
            'reason' => $this->faker->randomElement([
                'Réduction appliquée',
                'Cadeau échangé',
                'Remise fidélité'
            ]),
        ]);
    }
}