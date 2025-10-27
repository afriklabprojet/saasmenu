<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LanguageSeeder extends Seeder
{
    /**
     * Seed default languages for the application.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $languages = [
            [
                'name' => 'Français',
                'code' => 'fr',
                'layout' => 'ltr',
                'image' => null,
                'flag_icon' => '🇫🇷',
                'is_default' => '1',
                'is_available' => '1',
                'is_deleted' => '2',
                'is_active' => true,
                'rtl' => false,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'English',
                'code' => 'en',
                'layout' => 'ltr',
                'image' => null,
                'flag_icon' => '🇬🇧',
                'is_default' => '2',
                'is_available' => '1',
                'is_deleted' => '2',
                'is_active' => true,
                'rtl' => false,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'العربية',
                'code' => 'ar',
                'layout' => 'rtl',
                'image' => null,
                'flag_icon' => '🇸🇦',
                'is_default' => '2',
                'is_available' => '1',
                'is_deleted' => '2',
                'is_active' => true,
                'rtl' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Español',
                'code' => 'es',
                'layout' => 'ltr',
                'image' => null,
                'flag_icon' => '🇪🇸',
                'is_default' => '2',
                'is_available' => '1',
                'is_deleted' => '2',
                'is_active' => true,
                'rtl' => false,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($languages as $language) {
            // Vérifier si la langue existe déjà
            $exists = DB::table('languages')->where('code', $language['code'])->exists();
            
            if (!$exists) {
                DB::table('languages')->insert($language);
            } else {
                // Mettre à jour les nouvelles colonnes pour les langues existantes
                DB::table('languages')
                    ->where('code', $language['code'])
                    ->update([
                        'flag_icon' => $language['flag_icon'],
                        'is_active' => $language['is_active'],
                        'rtl' => $language['rtl'],
                        'sort_order' => $language['sort_order'],
                    ]);
            }
        }
    }
}
