<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use Illuminate\Database\Seeder;

class LoyaltyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding Loyalty Program Data...');

        $restaurants = Restaurant::all();
        $totalPrograms = 0;
        $totalMembers = 0;
        $totalTransactions = 0;

        foreach ($restaurants as $restaurant) {
            // Create main loyalty program
            $program = LoyaltyProgram::factory()->active()->create([
                'restaurant_id' => $restaurant->id,
                'name' => "{$restaurant->name} Rewards",
                'description' => "Earn points with every order at {$restaurant->name}",
                'type' => 'points',
            ]);

            $totalPrograms++;

            // Create VIP program for some restaurants
            if (rand(1, 100) <= 70) { // 70% chance
                LoyaltyProgram::factory()->vip()->create([
                    'restaurant_id' => $restaurant->id,
                    'name' => "{$restaurant->name} VIP Club",
                    'description' => "Exclusive rewards for our most valued customers",
                    'type' => 'tier',
                ]);
                $totalPrograms++;
            }

            // Create loyalty members
            $memberCount = rand(20, 50);
            $customers = User::factory($memberCount)->create([
                'role' => 'customer',
            ]);

            foreach ($customers as $customer) {
                $member = LoyaltyMember::create([
                    'program_id' => $program->id,
                    'user_id' => $customer->id,
                    'restaurant_id' => $restaurant->id,
                    'member_number' => 'LM' . $restaurant->id . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
                    'points_balance' => rand(0, 1000),
                    'total_earned' => rand(500, 5000),
                    'total_redeemed' => rand(0, 2000),
                    'tier_level' => $this->getTierLevel(),
                    'joined_at' => fake()->dateTimeBetween('-2 years', '-1 month'),
                    'last_activity_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);

                $totalMembers++;

                // Create transactions for each member
                $transactionCount = rand(3, 15);
                for ($i = 0; $i < $transactionCount; $i++) {
                    $transaction = LoyaltyTransaction::factory()->create([
                        'program_id' => $program->id,
                        'member_id' => $member->id,
                        'restaurant_id' => $restaurant->id,
                        'user_id' => $customer->id,
                    ]);

                    $totalTransactions++;
                }

                // Create some redemption transactions
                $redemptionCount = rand(0, 3);
                for ($j = 0; $j < $redemptionCount; $j++) {
                    LoyaltyTransaction::factory()->redemption()->create([
                        'program_id' => $program->id,
                        'member_id' => $member->id,
                        'restaurant_id' => $restaurant->id,
                        'user_id' => $customer->id,
                    ]);

                    $totalTransactions++;
                }

                // Create bonus transactions for some members
                if (rand(1, 100) <= 30) { // 30% chance
                    LoyaltyTransaction::factory()->bonus()->create([
                        'program_id' => $program->id,
                        'member_id' => $member->id,
                        'restaurant_id' => $restaurant->id,
                        'user_id' => $customer->id,
                    ]);

                    $totalTransactions++;
                }
            }

            $this->command->info("   ✓ Created loyalty programs and {$memberCount} members for {$restaurant->name}");
        }

        $this->command->info("✅ Created {$totalPrograms} programs, {$totalMembers} members, and {$totalTransactions} transactions");
    }

    private function getTierLevel(): string
    {
        $tiers = ['Bronze', 'Silver', 'Gold', 'Platinum'];
        $weights = [50, 30, 15, 5]; // Bronze most common, Platinum least common

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $tiers[$index];
            }
        }

        return 'Bronze';
    }
}
