<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Helpers\TranslationHelper;

class TestLocalization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'localization:test {--locale=fr}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste la localisation française du système RestroSaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locale = $this->option('locale');

        $this->info("🌍 Test de Localisation RestroSaaS");
        $this->info("=====================================");

        // Configuration de la locale
        App::setLocale($locale);
        if ($locale === 'fr') {
            TranslationHelper::setFrenchLocale();
        }

        $this->info("📍 Locale configurée: " . App::getLocale());
        $this->newLine();

        // Test des traductions admin
        $this->testAdminTranslations();

        // Test des traductions de notifications
        $this->testNotificationTranslations();

        // Test des traductions de formation
        $this->testTrainingTranslations();

        // Test des traductions de commandes
        $this->testCommandTranslations();

        // Test des helpers de traduction
        $this->testTranslationHelpers();

        // Test de formatage des dates et nombres
        $this->testFormatting();

        // Statistiques des fichiers
        $this->showTranslationStats();

        $this->newLine();
        $this->info("✅ Test de localisation terminé avec succès!");

        return 0;
    }

    /**
     * Teste les traductions d'administration
     */
    private function testAdminTranslations()
    {
        $this->info("🔧 Test Traductions Admin:");
        $this->line("  Dashboard: " . __('admin.dashboard'));
        $this->line("  Commandes: " . __('admin.orders'));
        $this->line("  Restaurants: " . __('admin.restaurants'));
        $this->line("  Clients: " . __('admin.customers'));
        $this->line("  Paramètres: " . __('admin.settings'));
        $this->newLine();
    }

    /**
     * Teste les traductions de notifications
     */
    private function testNotificationTranslations()
    {
        $this->info("🔔 Test Traductions Notifications:");
        $this->line("  Alerte système: " . __('notifications.types.system_alert'));
        $this->line("  Mise à jour commande: " . __('notifications.types.order_update'));
        $this->line("  Paiement reçu: " . __('notifications.types.payment_received'));
        $this->line("  Nouvelle inscription: " . __('notifications.types.new_registration'));
        $this->newLine();
    }

    /**
     * Teste les traductions de formation
     */
    private function testTrainingTranslations()
    {
        $this->info("🎓 Test Traductions Formation:");
        $this->line("  Opérations de base: " . __('training.modules.basic_operations'));
        $this->line("  Gestion commandes: " . __('training.modules.order_management'));
        $this->line("  Service client: " . __('training.modules.customer_service'));
        $this->line("  Rapports financiers: " . __('training.modules.financial_reports'));
        $this->newLine();
    }

    /**
     * Teste les traductions de commandes
     */
    private function testCommandTranslations()
    {
        $this->info("⚡ Test Traductions Commandes:");
        $this->line("  Surveillance démarrée: " . __('commands.monitoring.started'));
        $this->line("  Backup créé: " . __('commands.backup.created'));
        $this->line("  Test performance terminé: " . __('commands.performance.completed'));
        $this->line("  Notification envoyée: " . __('commands.notifications.sent'));
        $this->newLine();
    }

    /**
     * Teste les helpers de traduction
     */
    private function testTranslationHelpers()
    {
        $this->info("🛠️  Test Helpers Traduction:");
        $this->line("  Status 'active': " . TranslationHelper::translateStatus('active'));
        $this->line("  Status 'pending': " . TranslationHelper::translateStatus('pending'));
        $this->line("  Type 'admin': " . TranslationHelper::translateUserType('admin'));
        $this->line("  Type 'customer': " . TranslationHelper::translateUserType('customer'));
        $this->newLine();
    }

    /**
     * Teste le formatage français
     */
    private function testFormatting()
    {
        $this->info("📊 Test Formatage Français:");
        $this->line("  Date actuelle: " . TranslationHelper::formatDate(now()));
        $this->line("  Date relative: " . TranslationHelper::formatDateRelative(now()->subDays(2)));
        $this->line("  Prix: " . TranslationHelper::formatPrice(1299.99));
        $this->line("  Nombre: " . TranslationHelper::formatNumber(1234567.89));
        $this->newLine();
    }

    /**
     * Affiche les statistiques des fichiers de traduction
     */
    private function showTranslationStats()
    {
        $this->info("📈 Statistiques Fichiers Traduction:");

        $files = [
            'admin.php' => 'Interface Administration',
            'notifications.php' => 'Système Notifications',
            'training.php' => 'Module Formation',
            'commands.php' => 'Commandes CLI',
            'validation.php' => 'Validation Laravel'
        ];

        $totalTranslations = 0;

        foreach ($files as $filename => $description) {
            $path = resource_path("lang/fr/$filename");

            if (file_exists($path)) {
                $content = file_get_contents($path);
                $translations = substr_count($content, "'=>");
                $size = filesize($path);
                $totalTranslations += $translations;

                $this->line("  ✅ $description: $translations traductions (" .
                           number_format($size/1024, 1) . " KB)");
            } else {
                $this->line("  ❌ $description: Fichier manquant");
            }
        }

        $this->newLine();
        $this->info("🎯 Total: $totalTranslations traductions françaises chargées");
    }
}
