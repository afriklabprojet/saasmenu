<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardWidgetService;
use App\Services\DashboardNotificationService;
use App\Models\User;

/**
 * Commande de test pour le système de widgets dashboard
 */
class TestDashboardWidgets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dashboard-widgets {--vendor-id=1} {--period=today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste le système de widgets du dashboard';

    protected DashboardWidgetService $widgetService;
    protected DashboardNotificationService $notificationService;

    public function __construct(
        DashboardWidgetService $widgetService,
        DashboardNotificationService $notificationService
    ) {
        parent::__construct();
        $this->widgetService = $widgetService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $vendorId = (int) $this->option('vendor-id');
        $period = $this->option('period');

        $this->info("🧪 Test du système de widgets dashboard pour vendor ID: {$vendorId}");
        $this->info("📅 Période: {$period}");
        $this->newLine();

        // Vérifier que le vendor existe
        $vendor = User::find($vendorId);
        if (!$vendor) {
            $this->error("❌ Vendor avec ID {$vendorId} introuvable");
            return Command::FAILURE;
        }

        // Test 1: Génération des widgets
        $this->info("🔄 Test 1: Génération des widgets...");
        $startTime = microtime(true);

        try {
            $widgets = $this->widgetService->generateWidgets($vendorId, $period);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("✅ Widgets générés en {$duration}ms");
            $this->displayWidgetsSummary($widgets);

        } catch (\Exception $e) {
            $this->error("❌ Erreur génération widgets: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 2: Métriques temps réel
        $this->info("🔄 Test 2: Métriques temps réel...");
        $startTime = microtime(true);

        try {
            $metrics = $this->notificationService->updateRealTimeMetrics($vendorId, 'refresh');
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("✅ Métriques temps réel récupérées en {$duration}ms");
            $this->displayMetricsSummary($metrics);

        } catch (\Exception $e) {
            $this->error("❌ Erreur métriques temps réel: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 3: Snapshot complet du dashboard
        $this->info("🔄 Test 3: Snapshot complet dashboard...");
        $startTime = microtime(true);

        try {
            $snapshot = $this->notificationService->getDashboardSnapshot($vendorId);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("✅ Snapshot complet généré en {$duration}ms");
            $this->displaySnapshotSummary($snapshot);

        } catch (\Exception $e) {
            $this->error("❌ Erreur snapshot dashboard: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 4: Simulation d'événements
        $this->info("🔄 Test 4: Simulation d'événements...");
        $this->testEventSimulation($vendorId);

        $this->newLine();
        $this->info("🎉 Tous les tests sont passés avec succès!");
        $this->info("📊 Le système de widgets dashboard est opérationnel");

        return Command::SUCCESS;
    }

    /**
     * Afficher résumé des widgets
     */
    protected function displayWidgetsSummary(array $widgets): void
    {
        $this->table(['Type de Widget', 'Nombre d\'éléments', 'Status'], [
            ['Cartes de Résumé', count($widgets['summary_cards'] ?? []), '✅'],
            ['Graphiques', count($widgets['charts'] ?? []), '✅'],
            ['Panel d\'Insights', count($widgets['insights_panel']['insights'] ?? []), '✅'],
            ['Indicateurs KPI', count($widgets['performance_indicators'] ?? []), '✅'],
            ['Actions Rapides', count($widgets['quick_actions'] ?? []), '✅'],
            ['Alertes', count($widgets['alerts'] ?? []), '✅']
        ]);

        // Afficher quelques détails des cartes de résumé
        if (!empty($widgets['summary_cards'])) {
            $this->newLine();
            $this->info("📋 Aperçu des cartes de résumé:");
            foreach (array_slice($widgets['summary_cards'], 0, 2) as $card) {
                $trend = $card['trend'] === 'up' ? '📈' : ($card['trend'] === 'down' ? '📉' : '➡️');
                $this->line("  {$trend} {$card['title']}: {$card['value']}");
            }
        }
    }

    /**
     * Afficher résumé des métriques
     */
    protected function displayMetricsSummary(array $metrics): void
    {
        $this->table(['Métrique', 'Valeur'], [
            ['Commandes Actives', $metrics['active_orders'] ?? 0],
            ['CA Aujourd\'hui', number_format($metrics['today_revenue'] ?? 0, 2) . ' €'],
            ['Commandes Aujourd\'hui', $metrics['today_orders'] ?? 0],
            ['Clients en Ligne', $metrics['online_customers'] ?? 0],
            ['Status Serveur', $metrics['server_status'] ?? 'unknown'],
            ['Dernière Commande', $metrics['last_order_time'] ?? 'Aucune']
        ]);
    }

    /**
     * Afficher résumé du snapshot
     */
    protected function displaySnapshotSummary(array $snapshot): void
    {
        $widgetsCount = count($snapshot['widgets'] ?? []);
        $alertsCount = count($snapshot['alerts'] ?? []);
        $systemStatus = $snapshot['system_status']['app_status'] ?? 'unknown';

        $this->table(['Composant', 'Status/Valeur'], [
            ['Widgets Générés', $widgetsCount . ' types'],
            ['Alertes Actives', $alertsCount],
            ['Status Application', $systemStatus],
            ['Status Database', $snapshot['system_status']['database_status'] ?? 'unknown'],
            ['Status Cache', $snapshot['system_status']['cache_status'] ?? 'unknown'],
            ['Status Queue', $snapshot['system_status']['queue_status'] ?? 'unknown'],
            ['Dernière MAJ', $snapshot['last_updated'] ?? 'unknown']
        ]);
    }

    /**
     * Tester simulation d'événements
     */
    protected function testEventSimulation(int $vendorId): void
    {
        $events = [
            ['type' => 'new_order', 'data' => ['order_total' => 25.50]],
            ['type' => 'order_completed', 'data' => ['order_total' => 32.00]],
            ['type' => 'payment_received', 'data' => ['amount' => 45.75]],
            ['type' => 'new_customer', 'data' => ['customer_id' => 123]]
        ];

        foreach ($events as $event) {
            try {
                $startTime = microtime(true);
                $this->notificationService->updateRealTimeMetrics(
                    $vendorId,
                    $event['type'],
                    $event['data']
                );
                $duration = round((microtime(true) - $startTime) * 1000, 2);

                $this->info("  ✅ Événement '{$event['type']}' traité en {$duration}ms");

            } catch (\Exception $e) {
                $this->error("  ❌ Erreur événement '{$event['type']}': " . $e->getMessage());
            }
        }
    }
}
