<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\ImportJob;
use App\Models\ExportJob;
use Illuminate\Database\Seeder;

class ImportExportJobSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('📊 Seeding Import/Export Jobs...');

        $restaurants = Restaurant::all();
        $totalImports = 0;
        $totalExports = 0;

        foreach ($restaurants as $restaurant) {
            // Get restaurant admin user
            $adminUser = User::whereHas('restaurantUsers', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id)
                      ->where('role', 'admin');
            })->first();

            if (!$adminUser) {
                continue; // Skip if no admin user found
            }

            // Create completed import jobs (historical data)
            $completedImports = rand(3, 8);
            for ($i = 0; $i < $completedImports; $i++) {
                ImportJob::factory()->completed()->create([
                    'user_id' => $adminUser->id,
                    'restaurant_id' => $restaurant->id,
                    'data_type' => fake()->randomElement(['menus', 'customers', 'categories']),
                ]);
                $totalImports++;
            }

            // Create some failed imports for testing error handling
            $failedImports = rand(1, 3);
            for ($i = 0; $i < $failedImports; $i++) {
                ImportJob::factory()->failed()->create([
                    'user_id' => $adminUser->id,
                    'restaurant_id' => $restaurant->id,
                    'data_type' => fake()->randomElement(['menus', 'customers', 'orders']),
                    'errors' => [
                        ['row' => 1, 'error' => 'Invalid file format'],
                        ['row' => 5, 'error' => 'Missing required field: name'],
                        ['row' => 12, 'error' => 'Invalid price format'],
                    ],
                ]);
                $totalImports++;
            }

            // Create pending import job for testing
            ImportJob::factory()->pending()->create([
                'user_id' => $adminUser->id,
                'restaurant_id' => $restaurant->id,
                'data_type' => 'menus',
                'file_name' => 'menu_import_' . date('Y-m-d') . '.csv',
                'total_rows' => rand(50, 200),
            ]);
            $totalImports++;

            // Create completed export jobs
            $completedExports = rand(5, 12);
            for ($i = 0; $i < $completedExports; $i++) {
                ExportJob::factory()->completed()->create([
                    'user_id' => $adminUser->id,
                    'restaurant_id' => $restaurant->id,
                    'data_type' => fake()->randomElement(['orders', 'customers', 'menus', 'analytics']),
                    'format' => fake()->randomElement(['csv', 'excel', 'json']),
                ]);
                $totalExports++;
            }

            // Create recent export jobs with different formats
            $formats = ['csv', 'excel', 'json'];
            foreach ($formats as $format) {
                ExportJob::factory()->completed()->create([
                    'user_id' => $adminUser->id,
                    'restaurant_id' => $restaurant->id,
                    'data_type' => 'orders',
                    'format' => $format,
                    'file_name' => "orders_export_" . date('Y-m-d') . "_" . time() . "." . ($format === 'excel' ? 'xlsx' : $format),
                    'filters' => [
                        'date_from' => date('Y-m-01'), // Current month
                        'date_to' => date('Y-m-d'),
                        'status' => 'completed',
                    ],
                    'total_records' => rand(100, 500),
                    'exported_records' => function($attributes) {
                        return $attributes['total_records'];
                    },
                ]);
                $totalExports++;
            }

            // Create pending export job
            ExportJob::factory()->pending()->create([
                'user_id' => $adminUser->id,
                'restaurant_id' => $restaurant->id,
                'data_type' => 'customers',
                'format' => 'excel',
                'total_records' => rand(200, 800),
                'filters' => [
                    'date_from' => date('Y-01-01'), // This year
                    'date_to' => date('Y-m-d'),
                    'status' => 'active',
                ],
            ]);
            $totalExports++;

            // Create failed export job for testing
            ExportJob::factory()->failed()->create([
                'user_id' => $adminUser->id,
                'restaurant_id' => $restaurant->id,
                'data_type' => 'analytics',
                'format' => 'json',
                'total_records' => 0,
            ]);
            $totalExports++;

            // Create large dataset export (for performance testing)
            ExportJob::factory()->completed()->create([
                'user_id' => $adminUser->id,
                'restaurant_id' => $restaurant->id,
                'data_type' => 'orders',
                'format' => 'csv',
                'file_name' => "large_orders_export_" . date('Y') . ".csv",
                'total_records' => rand(5000, 15000),
                'exported_records' => function($attributes) {
                    return $attributes['total_records'];
                },
                'file_size' => rand(1048576, 10485760), // 1MB to 10MB
                'filters' => [
                    'date_from' => date('Y-01-01'),
                    'date_to' => date('Y-12-31'),
                ],
            ]);
            $totalExports++;

            $this->command->info("   ✓ Created import/export jobs for {$restaurant->name}");
        }

        $this->command->info("✅ Created {$totalImports} import jobs and {$totalExports} export jobs");
    }
}
