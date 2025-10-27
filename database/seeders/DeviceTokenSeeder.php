<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Database\Seeder;

class DeviceTokenSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('🔔 Seeding Device Tokens...');

        $restaurants = Restaurant::all();
        $totalTokens = 0;

        foreach ($restaurants as $restaurant) {
            // Get restaurant users
            $restaurantUsers = User::whereHas('restaurantUsers', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })->get();

            foreach ($restaurantUsers as $user) {
                // Each user can have multiple devices
                $deviceCount = rand(1, 3); // 1-3 devices per user

                for ($i = 0; $i < $deviceCount; $i++) {
                    $platform = fake()->randomElement(['android', 'ios', 'web']);

                    // Create active device tokens
                    DeviceToken::factory()->active()->create([
                        'user_id' => $user->id,
                        'restaurant_id' => $restaurant->id,
                        'platform' => $platform,
                    ]);
                    $totalTokens++;
                }

                // Add some inactive tokens (old devices)
                if (rand(1, 100) <= 40) { // 40% chance of having old devices
                    $oldDeviceCount = rand(1, 2);
                    for ($j = 0; $j < $oldDeviceCount; $j++) {
                        DeviceToken::factory()->inactive()->create([
                            'user_id' => $user->id,
                            'restaurant_id' => $restaurant->id,
                            'platform' => fake()->randomElement(['android', 'ios']),
                        ]);
                        $totalTokens++;
                    }
                }
            }

            // Create some customer device tokens (for loyalty notifications)
            $customerTokens = rand(20, 50);
            $customers = User::factory($customerTokens)->create(['role' => 'customer']);

            foreach ($customers as $customer) {
                // Primary device
                DeviceToken::factory()->active()->create([
                    'user_id' => $customer->id,
                    'restaurant_id' => $restaurant->id,
                    'platform' => fake()->randomElement(['android', 'ios']),
                ]);
                $totalTokens++;

                // Sometimes secondary device
                if (rand(1, 100) <= 30) { // 30% have second device
                    DeviceToken::factory()->active()->create([
                        'user_id' => $customer->id,
                        'restaurant_id' => $restaurant->id,
                        'platform' => 'web',
                    ]);
                    $totalTokens++;
                }
            }

            // Create some recently used tokens for testing real-time notifications
            $recentTokens = DeviceToken::where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->take(rand(5, 10))
                ->get();

            foreach ($recentTokens as $token) {
                $token->update([
                    'last_used_at' => fake()->dateTimeBetween('-1 hour', 'now'),
                ]);
            }

            $restaurantTokenCount = DeviceToken::where('restaurant_id', $restaurant->id)->count();
            $this->command->info("   ✓ Created {$restaurantTokenCount} device tokens for {$restaurant->name}");
        }

        // Create platform distribution summary
        $androidCount = DeviceToken::where('platform', 'android')->count();
        $iosCount = DeviceToken::where('platform', 'ios')->count();
        $webCount = DeviceToken::where('platform', 'web')->count();
        $activeCount = DeviceToken::where('is_active', true)->count();

        $this->command->info("✅ Created {$totalTokens} device tokens total");
        $this->command->info("   📱 Android: {$androidCount} | iOS: {$iosCount} | Web: {$webCount}");
        $this->command->info("   ✅ Active: {$activeCount} | Inactive: " . ($totalTokens - $activeCount));
    }
}
