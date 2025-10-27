<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:setup
                            {--email=admin@emenu.com : Email de l\'administrateur}
                            {--password=admin123 : Mot de passe}
                            {--name=Administrateur E-menu : Nom de l\'administrateur}';

    /**
     * The console command description.
     */
    protected $description = 'Configure le compte administrateur pour E-menu WhatsApp SaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        $this->info("🍽️ Configuration Administrateur E-menu WhatsApp SaaS");
        $this->info("===============================================");
        $this->newLine();

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $email)->first();

        if ($user) {
            if ($this->confirm("L'utilisateur {$email} existe déjà. Voulez-vous le mettre à jour ?", false)) {
                $user->update([
                    'name' => $name,
                    'password' => Hash::make($password),
                    'type' => 1, // Admin
                    'is_available' => 1,
                    'is_verified' => 1,
                    'email_verified_at' => now(),
                ]);
                $this->info("✅ Utilisateur mis à jour avec succès !");
            } else {
                $this->info("❌ Opération annulée.");
                return 1;
            }
        } else {
            // Créer nouvel utilisateur
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'type' => 1, // Admin
                'is_available' => 1,
                'is_verified' => 1,
                'login_type' => 'email',
                'email_verified_at' => now(),
            ]);
            $this->info("✅ Nouvel utilisateur créé avec succès !");
        }

        // Configuration des paramètres E-menu
        $this->setupEmenuSettings($user->id);

        // Attribution plan premium
        $this->assignPremiumPlan($user->id);

        $this->newLine();
        $this->info("🎉 Configuration terminée avec succès !");
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Email', $email],
                ['Mot de passe', $password],
                ['Nom', $name],
                ['Type', 'Administrateur'],
                ['Plateforme', 'E-menu WhatsApp SaaS'],
                ['Plan', 'Premium Lifetime'],
                ['Statut', 'Actif et Vérifié'],
            ]
        );

        return 0;
    }

    /**
     * Configure les paramètres E-menu WhatsApp
     */
    private function setupEmenuSettings(int $userId): void
    {
        $this->info("🔧 Configuration des paramètres E-menu WhatsApp...");

        // Mise à jour des paramètres globaux (vendor_id = 1)
        DB::table('settings')->where('vendor_id', 1)->update([
            'website_title' => 'E-menu WhatsApp SaaS',
            'landing_website_title' => 'E-menu - Menu Numérique WhatsApp',
            'meta_title' => 'E-menu - Système de Menu Numérique avec WhatsApp',
            'meta_description' => 'Solution complète de menu numérique avec notifications WhatsApp et paiements CinetPay',
            'language' => 'fr',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'primary_color' => '#25D366', // Vert WhatsApp
            'secondary_color' => '#128C7E', // Vert foncé WhatsApp
            'copyright' => '© 2025 E-menu - WhatsApp SaaS',
            'updated_at' => now()
        ]);

        // Vérifier si les paramètres utilisateur existent
        $existingSettings = DB::table('settings')->where('vendor_id', $userId)->first();

        if (!$existingSettings) {
            DB::table('settings')->insert([
                'vendor_id' => $userId,
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
                'email' => $this->option('email'),
                'description' => 'Interface d\'administration E-menu avec WhatsApp Business',
                'contact' => '+225 00 00 00 00',
                'copyright' => '© 2025 E-menu - WhatsApp SaaS',
                'website_title' => 'E-menu Admin',
                'meta_title' => 'E-menu - Administration',
                'meta_description' => 'Interface d\'administration E-menu WhatsApp SaaS',
                'language' => 'fr',
                'template' => 'default',
                'template_type' => 1,
                'primary_color' => '#25D366',
                'secondary_color' => '#128C7E',
                'landing_website_title' => 'E-menu WhatsApp SaaS',
                'image_size' => 5,
                'time_format' => 'H:i',
                'date_format' => 'd/m/Y',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('settings')->where('vendor_id', $userId)->update([
                'currency' => 'XOF',
                'language' => 'fr',
                'timezone' => 'Africa/Abidjan',
                'primary_color' => '#25D366',
                'secondary_color' => '#128C7E',
                'copyright' => '© 2025 E-menu - WhatsApp SaaS',
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Attribue un plan premium lifetime
     */
    private function assignPremiumPlan(int $userId): void
    {
        $this->info("💎 Attribution du plan Premium Lifetime...");

        // Vérifier si une transaction existe déjà
        $existingTransaction = DB::table('transactions')
            ->where('vendor_id', $userId)
            ->where('payment_id', 'ADMIN_LIFETIME')
            ->first();

        if (!$existingTransaction) {
            DB::table('transactions')->insert([
                'vendor_id' => $userId,
                'plan_id' => 1,
                'payment_type' => 'offline',
                'payment_id' => 'ADMIN_LIFETIME',
                'amount' => 0,
                'status' => 2,
                'purchase_date' => now(),
                'expire_date' => now()->addYears(100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
