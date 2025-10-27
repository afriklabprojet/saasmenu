<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\POSTerminal;
use App\Models\POSSession;
use Illuminate\Database\Seeder;

class POSSystemSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('💳 Seeding POS System Data...');

        $restaurants = Restaurant::all();
        $totalTerminals = 0;
        $totalSessions = 0;

        foreach ($restaurants as $restaurant) {
            // Create POS terminals for each restaurant
            $terminalCount = rand(2, 4);

            for ($i = 1; $i <= $terminalCount; $i++) {
                $terminal = POSTerminal::factory()->create([
                    'restaurant_id' => $restaurant->id,
                    'name' => "Terminal {$i} - {$restaurant->name}",
                    'identifier' => "POS_{$restaurant->id}_{$i}",
                    'location' => $this->getTerminalLocation($i, $terminalCount),
                    'is_active' => $i <= ($terminalCount - 1), // Keep one terminal inactive for testing
                ]);

                $totalTerminals++;

                // Create active session for some terminals
                if ($terminal->is_active && rand(1, 100) <= 60) { // 60% chance of active session
                    POSSession::factory()->active()->create([
                        'terminal_id' => $terminal->id,
                        'restaurant_id' => $restaurant->id,
                    ]);
                    $totalSessions++;
                }

                // Create some completed sessions for history
                $completedSessionCount = rand(3, 8);
                for ($j = 0; $j < $completedSessionCount; $j++) {
                    POSSession::factory()->completed()->create([
                        'terminal_id' => $terminal->id,
                        'restaurant_id' => $restaurant->id,
                    ]);
                    $totalSessions++;
                }

                // Create some sessions with sales data
                $salesSessionCount = rand(2, 5);
                for ($k = 0; $k < $salesSessionCount; $k++) {
                    POSSession::factory()->withSales()->create([
                        'terminal_id' => $terminal->id,
                        'restaurant_id' => $restaurant->id,
                    ]);
                    $totalSessions++;
                }
            }

            $this->command->info("   ✓ Created {$terminalCount} terminals for {$restaurant->name}");
        }

        $this->command->info("✅ Created {$totalTerminals} POS terminals and {$totalSessions} sessions");
    }

    private function getTerminalLocation(int $terminalNumber, int $totalTerminals): string
    {
        $locations = [
            'Front Counter',
            'Bar Area',
            'Kitchen Display',
            'Cashier Station',
            'Manager Office',
            'Takeaway Counter',
            'Drive-Through',
            'Terrace Service'
        ];

        if ($terminalNumber === 1) {
            return 'Main Counter';
        }

        return $locations[($terminalNumber - 2) % count($locations)];
    }
}
