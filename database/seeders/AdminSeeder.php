<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Seed admin account for E-menu WhatsApp SaaS
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Email admin par défaut
        $adminEmail = 'admin@emenu.com';
        
        // Vérifier si l'admin existe déjà
        $existingAdmin = DB::table('users')->where('email', $adminEmail)->first();
        
        if ($existingAdmin) {
            $this->command->info('✅ Administrateur existe déjà avec l\'ID: ' . $existingAdmin->id);
            $adminId = $existingAdmin->id;
            
            // Mettre à jour l'admin existant
            DB::table('users')->where('id', $adminId)->update([
                'name' => 'Administrateur E-menu',
                'password' => Hash::make('admin123'),
                'type' => 1, // Admin
                'is_available' => 1,
                'is_verified' => 1,
                'email_verified_at' => $now,
                'login_type' => 'email',
                'allow_without_subscription' => 1,
                'available_on_landing' => 1,
                'free_plan' => 0,
                'is_delivery' => 1,
                'updated_at' => $now,
            ]);
            $this->command->info('✅ Administrateur mis à jour !');
        } else {
            // Créer le nouvel administrateur
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Administrateur E-menu',
                'email' => $adminEmail,
                'email_verified_at' => $now,
                'password' => Hash::make('admin123'),
                'type' => 1, // Admin
                'is_available' => 1,
                'is_verified' => 1,
                'login_type' => 'email',
                'allow_without_subscription' => 1,
                'available_on_landing' => 1,
                'free_plan' => 0,
                'is_delivery' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('✅ Administrateur créé avec l\'ID: ' . $adminId);
        }

        // Vérifier si les paramètres admin existent déjà
        $existingSettings = DB::table('settings')->where('vendor_id', $adminId)->first();
        
        if (!$existingSettings) {
            // Créer les paramètres spécifiques à l'admin
            DB::table('settings')->insert([
                'vendor_id' => $adminId,
                'currency' => 'XOF',
                'currency_position' => 'left',
                'currency_space' => 1,
                'decimal_separator' => 1,
                'currency_formate' => 2,
                'maintenance_mode' => 0,
                'checkout_login_required' => 1,
                'is_checkout_login_required' => 1,
                'delivery_type' => '1,2',
                'timezone' => 'Africa/Abidjan',
                'address' => 'Administration E-menu WhatsApp SaaS',
                'email' => $adminEmail,
                'description' => 'Plateforme E-menu avec notifications WhatsApp et paiements CinetPay',
                'contact' => '+225 00 00 00 00',
                'copyright' => '© 2025 E-menu - WhatsApp SaaS',
                'website_title' => 'E-menu Admin',
                'meta_title' => 'E-menu - Administration WhatsApp SaaS',
                'meta_description' => 'Interface d\'administration E-menu avec WhatsApp Business et CinetPay',
                'language' => 'fr',
                'template' => 'default',
                'template_type' => 1,
                'primary_color' => '#25D366', // Vert WhatsApp
                'secondary_color' => '#128C7E', // Vert foncé WhatsApp
                'landing_website_title' => 'E-menu WhatsApp SaaS',
                'image_size' => 5,
                'time_format' => 'H:i',
                'date_format' => 'd/m/Y',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('✅ Paramètres admin créés !');
        }

        // Vérifier si une transaction premium existe
        $existingTransaction = DB::table('transactions')
            ->where('vendor_id', $adminId)
            ->where('payment_id', 'ADMIN_LIFETIME')
            ->first();
            
        if (!$existingTransaction) {
            // Créer une transaction premium lifetime pour l'admin
            DB::table('transactions')->insert([
                'vendor_id' => $adminId,
                'plan_id' => 1, // Plan premium
                'payment_type' => 'offline',
                'payment_id' => 'ADMIN_LIFETIME',
                'amount' => 0,
                'status' => 2, // Payé
                'purchase_date' => $now,
                'expire_date' => $now->copy()->addYears(100),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('✅ Plan premium lifetime attribué !');
        }

        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════════════════════╗');
        $this->command->info('║   ✅ ADMINISTRATEUR E-MENU CONFIGURÉ AVEC SUCCÈS      ║');
        $this->command->info('╚═══════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info('📧 Email: ' . $adminEmail);
        $this->command->info('🔑 Mot de passe: admin123');
        $this->command->info('👤 Nom: Administrateur E-menu');
        $this->command->info('🎨 Branding: WhatsApp (Vert #25D366)');
        $this->command->info('🌍 Langue: Français');
        $this->command->info('💰 Devise: XOF (CFA)');
        $this->command->info('💎 Plan: Premium Lifetime');
        $this->command->info('');
    }
}
