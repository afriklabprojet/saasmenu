<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'layout' => 'ltr',
                'image' => 'en.png',
                'is_default' => 1, // English par défaut
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Français',
                'code' => 'fr',
                'layout' => 'ltr',
                'image' => 'fr.png',
                'is_default' => 2,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'layout' => 'rtl',
                'image' => 'ar.png',
                'is_default' => 2,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('languages')->insert($languages);
    }
}
