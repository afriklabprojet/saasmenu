<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceTestService;
use Illuminate\Support\Facades\Log;

class PerformanceTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:test
                            {--type=full : Type de test (full, basic, database, cache, endpoints)}
                            {--users=10 : Nombre d\'utilisateurs concurrents}
                            {--duration=60 : Durée du test en secondes}
                            {--save : Sauvegarder rapport dans fichier}
                            {--json : Sortie format JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécuter tests de performance RestroSaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Tests Performance RestroSaaS');
        $this->info('===============================');

        $testService = new PerformanceTestService();

        // Configuration depuis options
        $testService->setConcurrentUsers($this->option('users'))
                   ->setTestDuration($this->option('duration'));

        try {
            $this->info('⏳ Démarrage des tests...');
            $this->newLine();

            $startTime = microtime(true);
            $results = $testService->runFullPerformanceTest();
            $endTime = microtime(true);

            if ($this->option('json')) {
                $this->line(json_encode($results, JSON_PRETTY_PRINT));
                return;
            }

            $this->displayResults($results);

            if ($this->option('save')) {
                $this->saveReport($results);
            }

            $this->info('✅ Tests terminés avec succès');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors des tests: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Afficher résultats des tests
     */
    private function displayResults($results)
    {
        $this->newLine();
        $this->info('📊 RÉSULTATS DES TESTS PERFORMANCE');
        $this->info('==================================');

        // Score global
        $score = $results['performance_score'];
        $status = $results['status'];

        $statusIcon = match($status) {
            'excellent' => '🟢',
            'good' => '🟡',
            'needs_improvement' => '🔴',
            default => '⚪'
        };

        $this->newLine();
        $this->line("🎯 Score Performance: {$statusIcon} {$score}/100 ({$status})");
        $this->line("⏱️  Temps Total Tests: {$results['total_test_time_seconds']}s");
        $this->newLine();

        // Résultats détaillés
        if (isset($results['detailed_results']['homepage_response_time'])) {
            $this->comment('🏠 PERFORMANCE PAGE D\'ACCUEIL');
            $this->table(
                ['Métrique', 'Valeur', 'Statut'],
                [
                    ['Temps de réponse', $results['detailed_results']['homepage_response_time'] . ' ms', $this->getTimeStatus($results['detailed_results']['homepage_response_time'])],
                    ['Temps boot app', $results['detailed_results']['app_boot_time'] . ' ms', $this->getTimeStatus($results['detailed_results']['app_boot_time'])],
                    ['Mémoire de base', $results['detailed_results']['base_memory_usage'] . ' MB', $this->getMemoryStatus($results['detailed_results']['base_memory_usage'])]
                ]
            );
        }

        // Performance base de données
        if (isset($results['detailed_results']['database_performance'])) {
            $this->comment('🗄️  PERFORMANCE BASE DE DONNÉES');
            $db = $results['detailed_results']['database_performance'];
            $this->table(
                ['Test', 'Temps (ms)', 'Statut'],
                [
                    ['Connexion simple', $db['connection_time_ms'], $this->getTimeStatus($db['connection_time_ms'])],
                    ['Requête complexe', $db['complex_query_time_ms'], $this->getTimeStatus($db['complex_query_time_ms'])],
                    ['Insertion en lot', $db['bulk_insert_time_ms'], $this->getTimeStatus($db['bulk_insert_time_ms'])],
                    ['Recherche index', $db['index_search_time_ms'], $this->getTimeStatus($db['index_search_time_ms'])]
                ]
            );
        }

        // Performance cache
        if (isset($results['detailed_results']['cache_performance'])) {
            $this->comment('💾 PERFORMANCE CACHE');
            $cache = $results['detailed_results']['cache_performance'];
            $this->table(
                ['Opération', 'Temps (ms)', 'Statut'],
                [
                    ['Écriture 1000 items', $cache['cache_write_1000_items_ms'], $this->getTimeStatus($cache['cache_write_1000_items_ms'])],
                    ['Lecture 1000 items', $cache['cache_read_1000_items_ms'], $this->getTimeStatus($cache['cache_read_1000_items_ms'])],
                    ['Cache miss', $cache['cache_miss_time_ms'], $this->getTimeStatus($cache['cache_miss_time_ms'])]
                ]
            );
        }

        // Charge concurrente
        if (isset($results['detailed_results']['concurrent_load'])) {
            $this->comment('👥 TESTS CHARGE CONCURRENTE');
            $load = $results['detailed_results']['concurrent_load'];

            $tableData = [];
            foreach ($load as $userCount => $data) {
                $tableData[] = [
                    str_replace('_', ' ', $userCount),
                    $data['avg_response_time_ms'] . ' ms',
                    $data['requests_per_second'] . ' req/s',
                    $data['successful_requests'] . '/' . ($data['successful_requests'] + $data['failed_requests'])
                ];
            }

            $this->table(
                ['Utilisateurs', 'Temps Moyen', 'Req/Seconde', 'Succès/Total'],
                $tableData
            );
        }

        // Utilisation mémoire
        if (isset($results['detailed_results']['memory_usage'])) {
            $this->comment('🧠 UTILISATION MÉMOIRE');
            $memory = $results['detailed_results']['memory_usage'];
            $this->table(
                ['Métrique', 'Valeur (MB)'],
                [
                    ['Mémoire initiale', $memory['initial_memory_mb']],
                    ['Pic mémoire', $memory['peak_memory_mb']],
                    ['Mémoire finale', $memory['final_memory_mb']],
                    ['Augmentation max', $memory['memory_increase_mb']],
                    ['Mémoire libérée', $memory['memory_freed_mb']]
                ]
            );
        }

        // Issues et recommandations
        if (!empty($results['issues_found'])) {
            $this->newLine();
            $this->warn('⚠️  PROBLÈMES DÉTECTÉS:');
            foreach ($results['issues_found'] as $issue) {
                $this->error("  • {$issue}");
            }
        }

        if (!empty($results['recommendations'])) {
            $this->newLine();
            $this->info('💡 RECOMMANDATIONS:');
            foreach ($results['recommendations'] as $recommendation) {
                $this->line("  • {$recommendation}");
            }
        }

        // Environnement de test
        $this->newLine();
        $this->comment('🔧 ENVIRONNEMENT DE TEST');
        $env = $results['test_environment'];
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['PHP Version', $env['php_version']],
                ['Laravel Version', $env['laravel_version']],
                ['Memory Limit', $env['memory_limit']],
                ['Max Execution Time', $env['max_execution_time'] . 's']
            ]
        );
    }

    /**
     * Obtenir statut temps de réponse
     */
    private function getTimeStatus($timeMs)
    {
        if ($timeMs < 200) return '🟢 Excellent';
        if ($timeMs < 500) return '🟡 Bon';
        if ($timeMs < 1000) return '🟠 Acceptable';
        return '🔴 Lent';
    }

    /**
     * Obtenir statut mémoire
     */
    private function getMemoryStatus($memoryMB)
    {
        if ($memoryMB < 100) return '🟢 Excellent';
        if ($memoryMB < 256) return '🟡 Bon';
        if ($memoryMB < 512) return '🟠 Acceptable';
        return '🔴 Élevé';
    }

    /**
     * Sauvegarder rapport
     */
    private function saveReport($results)
    {
        $filename = 'performance-report-' . now()->format('Y-m-d-H-i-s') . '.json';
        $path = storage_path('logs/' . $filename);

        file_put_contents($path, json_encode($results, JSON_PRETTY_PRINT));

        $this->info("📄 Rapport sauvegardé: {$path}");
    }
}
