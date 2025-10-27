<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\TableQrCode;
use Illuminate\Database\Seeder;

class TableQrSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('📱 Seeding Table QR Codes...');

        $restaurants = Restaurant::all();
        $totalTables = 0;

        foreach ($restaurants as $restaurant) {
            $tableCount = rand(15, 30); // Each restaurant has 15-30 tables

            // Create main hall tables
            $mainHallTables = (int)($tableCount * 0.6); // 60% in main hall
            for ($i = 1; $i <= $mainHallTables; $i++) {
                TableQrCode::factory()->active()->create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => $i,
                    'table_name' => "Table {$i}",
                    'location' => 'Main Hall',
                    'capacity' => $this->getTableCapacity('main'),
                ]);
                $totalTables++;
            }

            // Create terrace tables
            $terraceTables = (int)($tableCount * 0.25); // 25% on terrace
            for ($i = $mainHallTables + 1; $i <= $mainHallTables + $terraceTables; $i++) {
                TableQrCode::factory()->terrace()->create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => $i,
                    'table_name' => "Terrace {$i}",
                    'capacity' => $this->getTableCapacity('terrace'),
                ]);
                $totalTables++;
            }

            // Create VIP tables
            $vipTables = (int)($tableCount * 0.1); // 10% VIP tables
            for ($i = $mainHallTables + $terraceTables + 1; $i <= $mainHallTables + $terraceTables + $vipTables; $i++) {
                TableQrCode::factory()->vipTable()->create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => $i,
                    'capacity' => $this->getTableCapacity('vip'),
                ]);
                $totalTables++;
            }

            // Create private rooms
            $privateTables = $tableCount - $mainHallTables - $terraceTables - $vipTables;
            for ($i = $mainHallTables + $terraceTables + $vipTables + 1; $i <= $tableCount; $i++) {
                TableQrCode::factory()->privateRoom()->create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => $i,
                    'capacity' => $this->getTableCapacity('private'),
                ]);
                $totalTables++;
            }

            // Make some tables popular (frequently scanned)
            $popularTableCount = rand(3, 6);
            $popularTables = TableQrCode::where('restaurant_id', $restaurant->id)
                ->inRandomOrder()
                ->take($popularTableCount)
                ->get();

            foreach ($popularTables as $table) {
                $table->update([
                    'scan_count' => rand(200, 800),
                    'last_scanned_at' => fake()->dateTimeBetween('-1 day', 'now'),
                ]);
            }

            // Make some tables recently scanned
            $recentTableCount = rand(5, 10);
            $recentTables = TableQrCode::where('restaurant_id', $restaurant->id)
                ->inRandomOrder()
                ->take($recentTableCount)
                ->get();

            foreach ($recentTables as $table) {
                $table->update([
                    'scan_count' => $table->scan_count + rand(1, 5),
                    'last_scanned_at' => fake()->dateTimeBetween('-2 hours', 'now'),
                ]);
            }

            // Make a few tables inactive for maintenance
            $inactiveTableCount = rand(1, 3);
            $inactiveTables = TableQrCode::where('restaurant_id', $restaurant->id)
                ->inRandomOrder()
                ->take($inactiveTableCount)
                ->get();

            foreach ($inactiveTables as $table) {
                $table->update([
                    'is_active' => false,
                    'notes' => fake()->randomElement([
                        'Under maintenance',
                        'Furniture repair needed',
                        'Reserved for special event',
                        'Cleaning in progress'
                    ]),
                ]);
            }

            $this->command->info("   ✓ Created {$tableCount} QR tables for {$restaurant->name}");
        }

        $this->command->info("✅ Created {$totalTables} table QR codes across all restaurants");
    }

    private function getTableCapacity(string $type): int
    {
        return match($type) {
            'main' => fake()->randomElement([2, 2, 4, 4, 4, 6]), // Mostly 2-4 people
            'terrace' => fake()->randomElement([2, 4, 6]), // Outdoor seating
            'vip' => fake()->randomElement([4, 6, 8, 10]), // Larger VIP tables
            'private' => fake()->randomElement([6, 8, 10, 12]), // Private dining
            default => 4,
        };
    }
}
