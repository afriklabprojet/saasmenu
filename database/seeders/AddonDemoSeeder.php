<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AddonDemoSeeder extends Seeder
{
    /**
     * Run the database seeds for addon demonstration
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting RestroSaaS Addons Demo Data Seeding...');

        // Seed in specific order to respect dependencies
        $this->call([
            RestaurantUserSeeder::class,
            POSSystemSeeder::class,
            LoyaltyProgramSeeder::class,
            TableQrSeeder::class,
            ImportExportJobSeeder::class,
            DeviceTokenSeeder::class,
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ All addon demo data has been seeded successfully!');
        $this->command->info('📊 You can now test all 8 addons with realistic data.');
        
        $this->displaySummary();
    }

    private function displaySummary(): void
    {
        $this->command->info('');
        $this->command->info('📋 Addon Demo Data Summary:');
        $this->command->info('─────────────────────────────────');
        $this->command->info('🏪 Restaurant Users & Permissions');
        $this->command->info('💳 POS Systems with Terminals & Sessions');
        $this->command->info('🎯 Loyalty Programs & Member Transactions');
        $this->command->info('📱 Table QR Codes with Scan Analytics');
        $this->command->info('📊 Import/Export Jobs with Sample Data');
        $this->command->info('🔔 Device Tokens & Push Notifications');
        $this->command->info('🔐 API Keys & Access Management');
        $this->command->info('─────────────────────────────────');
        $this->command->info('🎉 Ready for testing and demonstration!');
    }
}