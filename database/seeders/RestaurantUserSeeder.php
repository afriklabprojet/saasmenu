<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class RestaurantUserSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding Restaurant Users...');

        // Get existing restaurants or create some if none exist
        $restaurants = Restaurant::take(5)->get();
        
        if ($restaurants->isEmpty()) {
            $this->command->info('📍 Creating demo restaurants...');
            $restaurants = Restaurant::factory(3)->create([
                'name' => function() {
                    return fake()->randomElement([
                        'Pizza Palace',
                        'Burger Junction',
                        'Sushi Garden',
                        'Café Central',
                        'Steakhouse Elite'
                    ]);
                },
                'is_active' => true,
            ]);
        }

        foreach ($restaurants as $restaurant) {
            // Create restaurant admin
            $admin = User::factory()->create([
                'name' => "Admin {$restaurant->name}",
                'email' => "admin@" . strtolower(str_replace(' ', '', $restaurant->name)) . ".com",
                'role' => 'restaurant_admin',
            ]);

            // Create restaurant-user relationship
            RestaurantUser::create([
                'user_id' => $admin->id,
                'restaurant_id' => $restaurant->id,
                'role' => 'admin',
                'permissions' => [
                    'pos_access' => true,
                    'loyalty_access' => true,
                    'tableqr_access' => true,
                    'import_export_access' => true,
                    'notifications_access' => true,
                    'analytics_access' => true,
                    'settings_access' => true,
                    'user_management' => true,
                ],
                'is_active' => true,
            ]);

            // Create restaurant manager
            $manager = User::factory()->create([
                'name' => "Manager {$restaurant->name}",
                'email' => "manager@" . strtolower(str_replace(' ', '', $restaurant->name)) . ".com",
                'role' => 'restaurant_manager',
            ]);

            RestaurantUser::create([
                'user_id' => $manager->id,
                'restaurant_id' => $restaurant->id,
                'role' => 'manager',
                'permissions' => [
                    'pos_access' => true,
                    'loyalty_access' => true,
                    'tableqr_access' => true,
                    'import_export_access' => false,
                    'notifications_access' => true,
                    'analytics_access' => true,
                    'settings_access' => false,
                    'user_management' => false,
                ],
                'is_active' => true,
            ]);

            // Create staff members
            for ($i = 1; $i <= 3; $i++) {
                $staff = User::factory()->create([
                    'name' => "Staff {$i} - {$restaurant->name}",
                    'email' => "staff{$i}@" . strtolower(str_replace(' ', '', $restaurant->name)) . ".com",
                    'role' => 'restaurant_staff',
                ]);

                RestaurantUser::create([
                    'user_id' => $staff->id,
                    'restaurant_id' => $restaurant->id,
                    'role' => 'staff',
                    'permissions' => [
                        'pos_access' => true,
                        'loyalty_access' => false,
                        'tableqr_access' => true,
                        'import_export_access' => false,
                        'notifications_access' => false,
                        'analytics_access' => false,
                        'settings_access' => false,
                        'user_management' => false,
                    ],
                    'is_active' => true,
                ]);
            }

            // Create API keys for restaurant
            ApiKey::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $admin->id,
                'name' => 'Main API Access',
                'key' => 'rest_' . $restaurant->id . '_' . bin2hex(random_bytes(16)),
                'permissions' => [
                    'pos_api' => true,
                    'loyalty_api' => true,
                    'tableqr_api' => true,
                    'notifications_api' => true,
                    'analytics_api' => true,
                ],
                'rate_limit' => 1000,
                'is_active' => true,
            ]);

            // Create limited API key
            ApiKey::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $manager->id,
                'name' => 'POS Only Access',
                'key' => 'pos_' . $restaurant->id . '_' . bin2hex(random_bytes(12)),
                'permissions' => [
                    'pos_api' => true,
                    'loyalty_api' => false,
                    'tableqr_api' => true,
                    'notifications_api' => false,
                    'analytics_api' => false,
                ],
                'rate_limit' => 500,
                'is_active' => true,
            ]);

            $this->command->info("   ✓ Created users and API keys for {$restaurant->name}");
        }

        $userCount = RestaurantUser::count();
        $apiKeyCount = ApiKey::count();
        
        $this->command->info("✅ Created {$userCount} restaurant users and {$apiKeyCount} API keys");
    }
}