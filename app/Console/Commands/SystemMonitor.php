<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemMonitoringService;
use Illuminate\Support\Facades\Log;

class SystemMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:monitor
                            {--alerts : Afficher uniquement les alertes}
                            {--json : Sortie format JSON}
                            {--save : Sauvegarder dans fichier rapport}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Surveiller les métriques système RestroSaaS et générer des alertes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Surveillance Système RestroSaaS');
        $this->info('===================================');

        $monitoring = new SystemMonitoringService();
        $metrics = $monitoring->collectMetrics();

        if ($this->option('json')) {
            $this->line(json_encode($metrics, JSON_PRETTY_PRINT));
            return;
        }

        if ($this->option('alerts')) {
            $this->displayAlertsOnly($metrics);
            return;
        }

        $this->displayFullReport($metrics);

        if ($this->option('save')) {
            $this->saveReport($metrics);
        }

        $this->info('✅ Surveillance terminée');
    }

    /**
     * Afficher uniquement les alertes
     */
    private function displayAlertsOnly($metrics)
    {
        $this->warn('🚨 ALERTES SYSTÈME');

        // Vérifier alertes critiques
        $alerts = $this->checkAlerts($metrics);

        if (empty($alerts)) {
            $this->info('✅ Aucune alerte critique détectée');
        } else {
            foreach ($alerts as $alert) {
                $this->error("⚠️  {$alert}");
            }
        }
    }

    /**
     * Afficher rapport complet
     */
    private function displayFullReport($metrics)
    {
        // Métriques système
        $this->comment('📊 MÉTRIQUES SYSTÈME');
        $system = $metrics['system'];
        $this->table(
            ['Métrique', 'Valeur', 'Statut'],
            [
                ['Mémoire utilisée', $system['memory_usage']['current_mb'] . ' MB', $this->getMemoryStatus($system['memory_usage'])],
                ['Mémoire pic', $system['memory_usage']['peak_mb'] . ' MB', '📈'],
                ['Version PHP', $system['php_version'], '✅'],
                ['Version Laravel', $system['laravel_version'], '✅'],
                ['Espace disque libre', $system['disk_free_space_gb'] . ' GB', $this->getDiskStatus($system['disk_free_space_gb'])]
            ]
        );

        // Métriques base de données
        $this->comment('🗄️  MÉTRIQUES BASE DE DONNÉES');
        $database = $metrics['database'];
        if ($database['status'] === 'connected') {
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Temps de connexion', $database['connection_time_ms'] . ' ms'],
                    ['Utilisateurs totaux', $database['stats']['total_users']],
                    ['Commandes totales', $database['stats']['total_orders']],
                    ['Restaurants actifs', $database['stats']['total_vendors']]
                ]
            );
        } else {
            $this->error('❌ Erreur connexion base de données: ' . $database['error']);
        }

        // Métriques stockage
        $this->comment('💾 MÉTRIQUES STOCKAGE');
        $storage = $metrics['storage'];
        if (!isset($storage['error'])) {
            $this->table(
                ['Type', 'Taille', 'Statut'],
                [
                    ['Logs', $storage['logs_size_mb'] . ' MB', $this->getStorageStatus($storage['logs_size_mb'])],
                    ['Cache', $storage['cache_size_mb'] . ' MB', $this->getStorageStatus($storage['cache_size_mb'])],
                    ['Sessions', $storage['sessions_size_mb'] . ' MB', $this->getStorageStatus($storage['sessions_size_mb'])],
                    ['Uploads publics', $storage['public_uploads_status'], $storage['public_uploads_status'] === 'accessible' ? '✅' : '❌']
                ]
            );
        }

        // Alertes
        $alerts = $this->checkAlerts($metrics);
        if (!empty($alerts)) {
            $this->warn('🚨 ALERTES DÉTECTÉES:');
            foreach ($alerts as $alert) {
                $this->error("  ⚠️  {$alert}");
            }
        } else {
            $this->info('✅ Système en bon état - Aucune alerte');
        }
    }

    /**
     * Vérifier alertes
     */
    private function checkAlerts($metrics)
    {
        $alerts = [];

        // Alerte mémoire
        $memoryUsage = $metrics['system']['memory_usage']['current_mb'];
        if ($memoryUsage > 500) {
            $alerts[] = "Utilisation mémoire élevée: {$memoryUsage}MB";
        }

        // Alerte espace disque
        if ($metrics['system']['disk_free_space_gb'] < 1) {
            $alerts[] = "Espace disque critique: {$metrics['system']['disk_free_space_gb']}GB";
        }

        // Alerte base de données
        if (isset($metrics['database']['connection_time_ms']) && $metrics['database']['connection_time_ms'] > 1000) {
            $alerts[] = "Temps réponse DB élevé: {$metrics['database']['connection_time_ms']}ms";
        }

        return $alerts;
    }

    /**
     * Statut mémoire
     */
    private function getMemoryStatus($memory)
    {
        $current = $memory['current_mb'];
        if ($current < 100) return '✅';
        if ($current < 300) return '⚠️';
        return '❌';
    }

    /**
     * Statut disque
     */
    private function getDiskStatus($freeSpace)
    {
        if ($freeSpace > 5) return '✅';
        if ($freeSpace > 1) return '⚠️';
        return '❌';
    }

    /**
     * Statut stockage
     */
    private function getStorageStatus($size)
    {
        if ($size < 100) return '✅';
        if ($size < 500) return '⚠️';
        return '❌';
    }

    /**
     * Sauvegarder rapport
     */
    private function saveReport($metrics)
    {
        $filename = 'monitoring-report-' . now()->format('Y-m-d-H-i-s') . '.json';
        $path = storage_path('logs/' . $filename);

        file_put_contents($path, json_encode($metrics, JSON_PRETTY_PRINT));

        $this->info("📄 Rapport sauvegardé: {$path}");
    }
}
