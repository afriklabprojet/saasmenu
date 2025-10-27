<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Services\SystemMonitoringService;
use Illuminate\Support\Facades\Cache;

class NotificationManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:manage
                            {action : Action (send, test, history, stats, monitor, clear)}
                            {--type= : Type de notification}
                            {--message= : Message à envoyer}
                            {--priority=normal : Priorité (low, normal, high, critical)}
                            {--channels=* : Canaux de notification}
                            {--period=24h : Période pour stats}
                            {--filters=* : Filtres pour historique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestionnaire notifications temps réel RestroSaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Gestionnaire Notifications RestroSaaS');
        $this->info('====================================');

        $notificationService = new NotificationService();
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'send':
                    return $this->sendNotification($notificationService);
                case 'test':
                    return $this->testNotifications($notificationService);
                case 'history':
                    return $this->showHistory($notificationService);
                case 'stats':
                    return $this->showStats($notificationService);
                case 'monitor':
                    return $this->startMonitoring($notificationService);
                case 'clear':
                    return $this->clearNotifications();
                default:
                    $this->error('Action non reconnue. Actions: send, test, history, stats, monitor, clear');
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error('Erreur: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Envoyer notification manuelle
     */
    private function sendNotification(NotificationService $service)
    {
        $this->newLine();
        $this->info('📤 Envoi Notification');
        $this->info('====================');

        $type = $this->option('type');
        $message = $this->option('message');
        $priority = $this->option('priority');
        $channels = $this->option('channels');

        // Saisie interactive si options manquantes
        if (!$type) {
            $types = array_keys(NotificationService::TYPES);
            $type = $this->choice('Type de notification', $types);
        }

        if (!$message) {
            $message = $this->ask('Message de la notification');
        }

        if (empty($channels)) {
            $availableChannels = array_keys(NotificationService::CHANNELS);
            $channels = $this->choice('Canaux de diffusion', $availableChannels, null, null, true);
        }

        // Envoi
        $result = $service->send($type, $message, [], $priority, $channels);

        if ($result['success']) {
            $this->info('✅ Notification envoyée avec succès !');
            $this->table(['Information', 'Valeur'], [
                ['ID Notification', $result['notification_id']],
                ['Type', NotificationService::TYPES[$type]],
                ['Priorité', strtoupper($priority)],
                ['Canaux', implode(', ', $channels)],
                ['Timestamp', now()->format('d/m/Y H:i:s')]
            ]);

            // Détail résultats par canal
            $this->newLine();
            $this->comment('📊 Résultats par Canal:');
            $channelResults = [];
            foreach ($result['channels_results'] as $channel => $data) {
                $status = $data['success'] ? '✅ Succès' : '❌ Échec';
                $details = $data['success'] ?
                    (isset($data['recipients_count']) ? "({$data['recipients_count']} destinataires)" : '') :
                    "({$data['error']})";
                $channelResults[] = [$channel, $status, $details];
            }
            $this->table(['Canal', 'Statut', 'Détails'], $channelResults);

        } else {
            $this->error('❌ Erreur: ' . $result['error']);
            return 1;
        }

        return 0;
    }

    /**
     * Tester système notifications
     */
    private function testNotifications(NotificationService $service)
    {
        $this->newLine();
        $this->info('🧪 Test Système Notifications');
        $this->info('=============================');

        $this->line('Démarrage tests tous canaux...');
        $this->newLine();

        $result = $service->testNotificationSystem();

        if ($result['success']) {
            $this->info('✅ Tests terminés !');
            $this->newLine();

            $testResults = [];
            foreach ($result['test_results'] as $channel => $data) {
                $status = $data['success'] ? '✅ OK' : '❌ ÉCHEC';
                $details = $data['success'] ? 'Fonctionnel' : ($data['details']['error'] ?? 'Erreur inconnue');
                $testResults[] = [$data['name'], $status, $details];
            }

            $this->table(['Canal', 'Statut', 'Détails'], $testResults);

            // Résumé
            $total = count($result['test_results']);
            $success = count(array_filter($result['test_results'], fn($r) => $r['success']));
            $rate = round(($success / $total) * 100, 1);

            $this->newLine();
            $this->info("📊 Résumé: {$success}/{$total} canaux fonctionnels ({$rate}%)");

        } else {
            $this->error('❌ Erreur lors des tests');
            return 1;
        }

        return 0;
    }

    /**
     * Afficher historique
     */
    private function showHistory(NotificationService $service)
    {
        $this->newLine();
        $this->info('📚 Historique Notifications');
        $this->info('===========================');

        $filters = [];
        $filterOptions = $this->option('filters');

        // Parser filtres
        foreach ($filterOptions as $filter) {
            if (str_contains($filter, ':')) {
                [$key, $value] = explode(':', $filter, 2);
                $filters[$key] = $value;
            }
        }

        $history = $service->getNotificationHistory($filters);

        if ($history['success'] && !empty($history['notifications'])) {
            $this->line("Total: {$history['total']} notifications");
            $this->newLine();

            $historyData = [];
            foreach (array_slice($history['notifications'], 0, 20) as $notification) {
                $historyData[] = [
                    $notification['created_at']->format('d/m H:i'),
                    NotificationService::TYPES[$notification['type']] ?? $notification['type'],
                    strtoupper($notification['priority']),
                    substr($notification['message'], 0, 50) . '...',
                    $notification['status']
                ];
            }

            $this->table(['Date', 'Type', 'Priorité', 'Message', 'Statut'], $historyData);

            if ($history['total'] > 20) {
                $this->line("... et " . ($history['total'] - 20) . " autres notifications");
            }

        } else {
            $this->line('Aucune notification dans l\'historique.');
        }

        return 0;
    }

    /**
     * Afficher statistiques
     */
    private function showStats(NotificationService $service)
    {
        $this->newLine();
        $this->info('📊 Statistiques Notifications');
        $this->info('=============================');

        $period = $this->option('period');
        $stats = $service->getNotificationStats($period);

        if ($stats['success']) {
            $data = $stats['stats'];

            $this->newLine();
            $this->comment("📈 Période: {$data['period']}");
            $this->line("📤 Total envoyées: {$data['total_sent']}");
            $this->newLine();

            // Par type
            if (!empty($data['by_type'])) {
                $this->comment('📋 Par Type:');
                $typeData = [];
                foreach ($data['by_type'] as $type => $count) {
                    $typeName = NotificationService::TYPES[$type] ?? $type;
                    $percentage = $data['total_sent'] > 0 ? round(($count / $data['total_sent']) * 100, 1) : 0;
                    $typeData[] = [$typeName, $count, "{$percentage}%"];
                }
                $this->table(['Type', 'Nombre', '%'], $typeData);
            }

            // Par priorité
            if (!empty($data['by_priority'])) {
                $this->newLine();
                $this->comment('⚡ Par Priorité:');
                $priorityData = [];
                foreach ($data['by_priority'] as $priority => $count) {
                    $percentage = $data['total_sent'] > 0 ? round(($count / $data['total_sent']) * 100, 1) : 0;
                    $priorityData[] = [strtoupper($priority), $count, "{$percentage}%"];
                }
                $this->table(['Priorité', 'Nombre', '%'], $priorityData);
            }

        } else {
            $this->error('Erreur récupération statistiques');
            return 1;
        }

        return 0;
    }

    /**
     * Démarrer monitoring notifications
     */
    private function startMonitoring(NotificationService $service)
    {
        $this->newLine();
        $this->info('👁️ Monitoring Notifications Temps Réel');
        $this->info('======================================');

        $monitoringService = new SystemMonitoringService();

        $this->line('Démarrage surveillance...');
        $this->line('Appuyez sur Ctrl+C pour arrêter');
        $this->newLine();

        $iteration = 0;
        while (true) {
            $iteration++;

            // Vérifier métriques système
            $metrics = $monitoringService->checkSystemHealth();

            if (!$metrics['healthy']) {
                foreach ($metrics['issues'] as $issue) {
                    $this->warn("⚠️ Problème détecté: {$issue}");

                    // Envoyer notification automatique
                    $service->sendSystemAlert('warning', $issue, [
                        'iteration' => $iteration,
                        'timestamp' => now()
                    ]);
                }
            }

            // Affichage status
            if ($iteration % 12 === 0) { // Toutes les minutes (5s * 12)
                $this->line("[" . now()->format('H:i:s') . "] Surveillance active - Itération #{$iteration}");
            }

            sleep(5); // Vérification toutes les 5 secondes
        }

        return 0;
    }

    /**
     * Nettoyer notifications anciennes
     */
    private function clearNotifications()
    {
        $this->newLine();
        $this->info('🗑️ Nettoyage Notifications');
        $this->info('==========================');

        if (!$this->confirm('Supprimer toutes les notifications anciennes ?')) {
            $this->line('Opération annulée.');
            return 0;
        }

        // Nettoyer cache
        $keys = Cache::get('notification_keys', []);
        $deleted = 0;

        foreach ($keys as $key) {
            if (Cache::forget($key)) {
                $deleted++;
            }
        }

        Cache::forget('notification_keys');

        $this->info("✅ {$deleted} notifications supprimées");
        return 0;
    }
}
