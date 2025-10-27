<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    private $monitoring;

    public function __construct()
    {
        $this->monitoring = new SystemMonitoringService();
    }

    /**
     * Tableau de bord monitoring principal
     */
    public function dashboard()
    {
        $metrics = $this->monitoring->collectMetrics();

        // Métriques des dernières 24h depuis les logs
        $historicalMetrics = $this->getHistoricalMetrics();

        return view('admin.monitoring.dashboard', compact('metrics', 'historicalMetrics'));
    }

    /**
     * API endpoint pour métriques temps réel
     */
    public function apiMetrics()
    {
        $metrics = $this->monitoring->collectMetrics();
        return response()->json($metrics);
    }

    /**
     * Logs système en temps réel
     */
    public function logs(Request $request)
    {
        $logType = $request->get('type', 'laravel');
        $lines = $request->get('lines', 100);

        $logPath = storage_path("logs/{$logType}.log");

        if (!File::exists($logPath)) {
            return response()->json(['error' => 'Fichier log non trouvé'], 404);
        }

        $logs = $this->readLastLines($logPath, $lines);

        return response()->json([
            'logs' => $logs,
            'file' => $logType,
            'lines_requested' => $lines,
            'last_updated' => now()->toISOString()
        ]);
    }

    /**
     * Alertes système actives
     */
    public function alerts()
    {
        $metrics = $this->monitoring->collectMetrics();
        $alerts = $this->checkSystemAlerts($metrics);

        return response()->json([
            'alerts' => $alerts,
            'count' => count($alerts),
            'severity' => $this->getHighestSeverity($alerts),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Rapport de santé système
     */
    public function healthCheck()
    {
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabase(),
                'storage' => $this->checkStorage(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'memory' => $this->checkMemory()
            ],
            'timestamp' => now()->toISOString()
        ];

        // Déterminer statut global
        $failedChecks = array_filter($health['checks'], function($check) {
            return $check['status'] !== 'ok';
        });

        if (!empty($failedChecks)) {
            $health['status'] = count($failedChecks) > 2 ? 'critical' : 'warning';
        }

        return response()->json($health);
    }

    /**
     * Métriques historiques depuis les logs
     */
    private function getHistoricalMetrics()
    {
        $logPath = storage_path('logs/performance.log');

        if (!File::exists($logPath)) {
            return [];
        }

        $lines = $this->readLastLines($logPath, 1000);
        $metrics = [];

        foreach ($lines as $line) {
            if (strpos($line, 'execution_time_ms') !== false) {
                $decoded = json_decode(substr($line, strpos($line, '{')), true);
                if ($decoded && isset($decoded['execution_time_ms'])) {
                    $metrics[] = [
                        'time' => $decoded['timestamp'] ?? now()->toISOString(),
                        'execution_time' => $decoded['execution_time_ms'],
                        'memory_usage' => $decoded['memory_usage_mb'] ?? 0,
                        'url' => $decoded['url'] ?? 'unknown'
                    ];
                }
            }
        }

        return array_slice(array_reverse($metrics), 0, 100); // 100 dernières métriques
    }

    /**
     * Vérifications santé système
     */
    private function checkDatabase()
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'message' => 'Connexion active'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage()
    {
        $writable = is_writable(storage_path());
        return [
            'status' => $writable ? 'ok' : 'error',
            'message' => $writable ? 'Accessible en écriture' : 'Non accessible'
        ];
    }

    private function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 60);
            $test = Cache::get('health_check');
            return [
                'status' => $test === 'ok' ? 'ok' : 'error',
                'message' => $test === 'ok' ? 'Cache fonctionnel' : 'Erreur cache'
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue()
    {
        // Vérification simple de la configuration queue
        $driver = config('queue.default');
        return [
            'status' => 'ok',
            'message' => "Driver: {$driver}"
        ];
    }

    private function checkMemory()
    {
        $usage = memory_get_usage(true) / 1024 / 1024; // MB
        $limit = ini_get('memory_limit');

        if ($limit !== '-1') {
            $limitMB = (int) str_replace('M', '', $limit);
            $percentage = ($usage / $limitMB) * 100;

            if ($percentage > 80) {
                return ['status' => 'warning', 'message' => "Utilisation: {$usage}MB ({$percentage}%)"];
            }
        }

        return ['status' => 'ok', 'message' => "Utilisation: {$usage}MB"];
    }

    /**
     * Alertes système
     */
    private function checkSystemAlerts($metrics)
    {
        $alerts = [];

        // Alerte mémoire
        if ($metrics['system']['memory_usage']['current_mb'] > 500) {
            $alerts[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => 'Utilisation mémoire élevée',
                'value' => $metrics['system']['memory_usage']['current_mb'] . 'MB'
            ];
        }

        // Alerte espace disque
        if ($metrics['system']['disk_free_space_gb'] < 2) {
            $alerts[] = [
                'type' => 'disk',
                'severity' => 'critical',
                'message' => 'Espace disque faible',
                'value' => $metrics['system']['disk_free_space_gb'] . 'GB'
            ];
        }

        return $alerts;
    }

    private function getHighestSeverity($alerts)
    {
        $severities = array_column($alerts, 'severity');

        if (in_array('critical', $severities)) return 'critical';
        if (in_array('warning', $severities)) return 'warning';
        return 'info';
    }

    /**
     * Lire les dernières lignes d'un fichier
     */
    private function readLastLines($filename, $lines = 100)
    {
        if (!File::exists($filename)) {
            return [];
        }

        $handle = fopen($filename, 'r');
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) break;
        }
        fclose($handle);

        return array_reverse($text);
    }
}
