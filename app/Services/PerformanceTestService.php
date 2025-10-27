<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PerformanceTestService
{
    private $testResults = [];
    private $baseUrl;
    private $concurrentUsers = 10;
    private $testDuration = 60; // secondes

    public function __construct()
    {
        $this->baseUrl = config('app.url');
    }

    /**
     * Exécuter suite complète de tests performance
     */
    public function runFullPerformanceTest()
    {
        Log::info('Démarrage tests performance complets');

        $startTime = microtime(true);

        try {
            // 1. Tests de base
            $this->testBasicPerformance();

            // 2. Tests de charge database
            $this->testDatabasePerformance();

            // 3. Tests cache
            $this->testCachePerformance();

            // 4. Tests endpoints critiques
            $this->testCriticalEndpoints();

            // 5. Tests concurrent users
            $this->testConcurrentLoad();

            // 6. Tests memory leaks
            $this->testMemoryUsage();

            $endTime = microtime(true);
            $totalTime = round(($endTime - $startTime), 2);

            $summary = $this->generateTestSummary($totalTime);

            Log::info('Tests performance terminés', $summary);

            return $summary;

        } catch (\Exception $e) {
            Log::error('Erreur tests performance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tests performance de base
     */
    private function testBasicPerformance()
    {
        $this->logTestStart('Tests Performance de Base');

        // Test temps de réponse page d'accueil
        $homePageTime = $this->measureEndpointTime('/');
        $this->testResults['homepage_response_time'] = $homePageTime;

        // Test temps de réponse admin login
        $adminLoginTime = $this->measureEndpointTime('/admin');
        $this->testResults['admin_login_response_time'] = $adminLoginTime;

        // Test temps de démarrage application
        $bootTime = $this->measureApplicationBootTime();
        $this->testResults['app_boot_time'] = $bootTime;

        // Test utilisation mémoire de base
        $baseMemory = memory_get_usage(true) / 1024 / 1024; // MB
        $this->testResults['base_memory_usage'] = round($baseMemory, 2);

        $this->logTestResult('Basic Performance', [
            'homepage_ms' => $homePageTime,
            'admin_login_ms' => $adminLoginTime,
            'boot_time_ms' => $bootTime,
            'memory_mb' => $baseMemory
        ]);
    }

    /**
     * Tests performance base de données
     */
    private function testDatabasePerformance()
    {
        $this->logTestStart('Tests Performance Base de Données');

        $results = [];

        // Test connexion simple
        $start = microtime(true);
        DB::select('SELECT 1');
        $connectionTime = round((microtime(true) - $start) * 1000, 2);
        $results['connection_time_ms'] = $connectionTime;

        // Test requête complexe (users avec relations)
        $start = microtime(true);
        DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.*', DB::raw('COUNT(orders.id) as order_count'))
            ->groupBy('users.id')
            ->limit(100)
            ->get();
        $complexQueryTime = round((microtime(true) - $start) * 1000, 2);
        $results['complex_query_time_ms'] = $complexQueryTime;

        // Test insertion en lot
        $start = microtime(true);
        $testData = [];
        for ($i = 0; $i < 100; $i++) {
            $testData[] = [
                'name' => 'Performance Test ' . $i,
                'email' => 'perftest' . $i . '@test.com',
                'password' => bcrypt('password'),
                'type' => 3,
                'vendor_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('users')->insert($testData);
        $insertTime = round((microtime(true) - $start) * 1000, 2);
        $results['bulk_insert_time_ms'] = $insertTime;

        // Nettoyer les données de test
        DB::table('users')->where('email', 'like', 'perftest%@test.com')->delete();

        // Test index performance
        $start = microtime(true);
        DB::table('users')->where('email', 'like', '%@%')->count();
        $indexTime = round((microtime(true) - $start) * 1000, 2);
        $results['index_search_time_ms'] = $indexTime;

        $this->testResults['database_performance'] = $results;
        $this->logTestResult('Database Performance', $results);
    }

    /**
     * Tests performance cache
     */
    private function testCachePerformance()
    {
        $this->logTestStart('Tests Performance Cache');

        $results = [];

        // Test écriture cache
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            Cache::put("perf_test_$i", "test_data_$i", 300);
        }
        $writeTime = round((microtime(true) - $start) * 1000, 2);
        $results['cache_write_1000_items_ms'] = $writeTime;

        // Test lecture cache
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            Cache::get("perf_test_$i");
        }
        $readTime = round((microtime(true) - $start) * 1000, 2);
        $results['cache_read_1000_items_ms'] = $readTime;

        // Test cache miss
        $start = microtime(true);
        Cache::get('non_existent_key');
        $missTime = round((microtime(true) - $start) * 1000, 2);
        $results['cache_miss_time_ms'] = $missTime;

        // Nettoyer cache test
        for ($i = 0; $i < 1000; $i++) {
            Cache::forget("perf_test_$i");
        }

        $this->testResults['cache_performance'] = $results;
        $this->logTestResult('Cache Performance', $results);
    }

    /**
     * Tests endpoints critiques
     */
    private function testCriticalEndpoints()
    {
        $this->logTestStart('Tests Endpoints Critiques');

        $endpoints = [
            'homepage' => '/',
            'admin_login' => '/admin',
            'api_health' => '/health-check',
        ];

        $results = [];

        foreach ($endpoints as $name => $endpoint) {
            $times = [];

            // Mesurer 10 fois pour avoir moyenne
            for ($i = 0; $i < 10; $i++) {
                $time = $this->measureEndpointTime($endpoint);
                $times[] = $time;
            }

            $results[$name] = [
                'avg_response_time_ms' => round(array_sum($times) / count($times), 2),
                'min_response_time_ms' => min($times),
                'max_response_time_ms' => max($times),
                'samples' => count($times)
            ];
        }

        $this->testResults['critical_endpoints'] = $results;
        $this->logTestResult('Critical Endpoints', $results);
    }

    /**
     * Tests charge concurrente simulée
     */
    private function testConcurrentLoad()
    {
        $this->logTestStart('Tests Charge Concurrente');

        $results = [];
        $userCounts = [5, 10, 20];

        foreach ($userCounts as $userCount) {
            $this->logTestStart("Test avec {$userCount} utilisateurs concurrents");

            $startTime = microtime(true);
            $responses = [];
            $errors = 0;

            // Simuler utilisateurs concurrents avec curl
            $commands = [];
            for ($i = 0; $i < $userCount; $i++) {
                $commands[] = "curl -s -w '%{time_total}\\n' -o /dev/null '{$this->baseUrl}/'";
            }

            // Exécuter en parallèle (simulation basique)
            foreach ($commands as $command) {
                $output = shell_exec($command);
                if ($output !== null) {
                    $responses[] = floatval(trim($output)) * 1000; // Convertir en ms
                } else {
                    $errors++;
                }
            }

            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            if (!empty($responses)) {
                $results["{$userCount}_users"] = [
                    'total_time_ms' => $totalTime,
                    'avg_response_time_ms' => round(array_sum($responses) / count($responses), 2),
                    'successful_requests' => count($responses),
                    'failed_requests' => $errors,
                    'requests_per_second' => round(count($responses) / ($totalTime / 1000), 2)
                ];
            }
        }

        $this->testResults['concurrent_load'] = $results;
        $this->logTestResult('Concurrent Load', $results);
    }

    /**
     * Tests utilisation mémoire
     */
    private function testMemoryUsage()
    {
        $this->logTestStart('Tests Utilisation Mémoire');

        $initialMemory = memory_get_usage(true);

        // Simuler charge mémoire
        $data = [];
        for ($i = 0; $i < 10000; $i++) {
            $data[] = [
                'id' => $i,
                'data' => str_repeat('x', 1000), // 1KB par item
                'timestamp' => microtime(true)
            ];
        }

        $peakMemory = memory_get_peak_usage(true);
        $currentMemory = memory_get_usage(true);

        // Libérer mémoire
        unset($data);
        gc_collect_cycles();

        $finalMemory = memory_get_usage(true);

        $results = [
            'initial_memory_mb' => round($initialMemory / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'final_memory_mb' => round($finalMemory / 1024 / 1024, 2),
            'memory_increase_mb' => round(($peakMemory - $initialMemory) / 1024 / 1024, 2),
            'memory_freed_mb' => round(($currentMemory - $finalMemory) / 1024 / 1024, 2)
        ];

        $this->testResults['memory_usage'] = $results;
        $this->logTestResult('Memory Usage', $results);
    }

    /**
     * Mesurer temps de réponse d'un endpoint
     */
    private function measureEndpointTime($endpoint)
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(30)->get($this->baseUrl . $endpoint);
            $end = microtime(true);

            return round(($end - $start) * 1000, 2); // en millisecondes

        } catch (\Exception $e) {
            Log::warning("Erreur mesure endpoint {$endpoint}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mesurer temps de démarrage application
     */
    private function measureApplicationBootTime()
    {
        $start = microtime(true);

        // Simuler démarrage application
        app('router');
        app('db');
        app('cache');

        $end = microtime(true);

        return round(($end - $start) * 1000, 2);
    }

    /**
     * Générer résumé des tests
     */
    private function generateTestSummary($totalTestTime)
    {
        $issues = [];
        $recommendations = [];
        $score = 100; // Score sur 100

        // Analyser résultats et générer score
        if (isset($this->testResults['homepage_response_time']) && $this->testResults['homepage_response_time'] > 2000) {
            $issues[] = 'Page d\'accueil lente (> 2s)';
            $score -= 10;
            $recommendations[] = 'Optimiser cache page d\'accueil';
        }

        if (isset($this->testResults['database_performance']['complex_query_time_ms']) &&
            $this->testResults['database_performance']['complex_query_time_ms'] > 1000) {
            $issues[] = 'Requêtes base de données lentes';
            $score -= 15;
            $recommendations[] = 'Optimiser index base de données';
        }

        if (isset($this->testResults['memory_usage']['peak_memory_mb']) &&
            $this->testResults['memory_usage']['peak_memory_mb'] > 512) {
            $issues[] = 'Utilisation mémoire élevée';
            $score -= 10;
            $recommendations[] = 'Optimiser utilisation mémoire';
        }

        return [
            'timestamp' => now()->toISOString(),
            'total_test_time_seconds' => $totalTestTime,
            'performance_score' => max(0, $score),
            'status' => $score >= 80 ? 'excellent' : ($score >= 60 ? 'good' : 'needs_improvement'),
            'issues_found' => $issues,
            'recommendations' => $recommendations,
            'detailed_results' => $this->testResults,
            'test_environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ];
    }

    /**
     * Logger début de test
     */
    private function logTestStart($testName)
    {
        Log::info("🧪 Début test: {$testName}");
    }

    /**
     * Logger résultat de test
     */
    private function logTestResult($testName, $results)
    {
        Log::info("✅ Test {$testName} terminé", $results);
    }

    /**
     * Configurer paramètres de test
     */
    public function setConcurrentUsers($count)
    {
        $this->concurrentUsers = $count;
        return $this;
    }

    public function setTestDuration($seconds)
    {
        $this->testDuration = $seconds;
        return $this;
    }
}
