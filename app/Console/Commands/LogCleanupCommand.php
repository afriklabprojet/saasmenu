<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Commande de nettoyage automatique des logs
 * Supprime les anciens logs et optimise l'espace disque
 */
class LogCleanupCommand extends Command
{
    protected $signature = 'monitoring:cleanup
                           {--days=30 : Number of days to keep logs}
                           {--dry-run : Show what would be deleted without actually deleting}
                           {--force : Force cleanup without confirmation}';

    protected $description = 'Clean up old log files and monitoring data';

    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🧹 Starting log cleanup (keeping last {$days} days)...");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will be deleted');
        }

        $cutoffDate = Carbon::now()->subDays($days);
        $this->line("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->line('');

        // Nettoyer les fichiers de logs
        $logStats = $this->cleanupLogFiles($cutoffDate, $dryRun);

        // Nettoyer les données de cache de monitoring
        $cacheStats = $this->cleanupMonitoringCache($cutoffDate, $dryRun);

        // Nettoyer les métriques de base de données
        $dbStats = $this->cleanupDatabaseMetrics($cutoffDate, $dryRun);

        // Afficher le résumé
        $this->displayCleanupSummary($logStats, $cacheStats, $dbStats);

        if (!$dryRun && !$force) {
            if (!$this->confirm('Do you want to proceed with the cleanup?')) {
                $this->info('Cleanup cancelled');
                return Command::SUCCESS;
            }

            // Relancer le nettoyage réel
            return $this->call('monitoring:cleanup', [
                '--days' => $days,
                '--force' => true
            ]);
        }

        if (!$dryRun) {
            $this->info('✅ Log cleanup completed successfully');

            // Enregistrer l'événement de nettoyage
            $this->logCleanupEvent($logStats, $cacheStats, $dbStats);
        }

        return Command::SUCCESS;
    }

    /**
     * Nettoyer les fichiers de logs
     */
    private function cleanupLogFiles(Carbon $cutoffDate, bool $dryRun): array
    {
        $this->info('📁 Cleaning up log files...');

        $logPath = storage_path('logs');
        $deletedFiles = 0;
        $freedSpace = 0;
        $processedFiles = 0;

        if (!is_dir($logPath)) {
            $this->warn("Log directory not found: {$logPath}");
            return ['deleted' => 0, 'freed_space' => 0, 'processed' => 0];
        }

        $files = File::glob($logPath . '/*.log');
        $files = array_merge($files, File::glob($logPath . '/**/*.log'));

        foreach ($files as $file) {
            $processedFiles++;
            $fileTime = Carbon::createFromTimestamp(filemtime($file));
            $fileSize = filesize($file);

            if ($fileTime->lt($cutoffDate)) {
                $this->line("  📄 {$file} (created: {$fileTime->format('Y-m-d')}, size: " . $this->formatBytes($fileSize) . ")");

                if (!$dryRun) {
                    File::delete($file);
                }

                $deletedFiles++;
                $freedSpace += $fileSize;
            }
        }

        $this->line("  ✅ Processed {$processedFiles} files, deleted {$deletedFiles}, freed " . $this->formatBytes($freedSpace));

        return [
            'deleted' => $deletedFiles,
            'freed_space' => $freedSpace,
            'processed' => $processedFiles
        ];
    }

    /**
     * Nettoyer le cache de monitoring
     */
    private function cleanupMonitoringCache(Carbon $cutoffDate, bool $dryRun): array
    {
        $this->info('🗄️ Cleaning up monitoring cache...');

        $deletedKeys = 0;
        $freedMemory = 0;

        // Patterns de clés à nettoyer
        $patterns = [
            'system_metrics_*',
            'system_alerts_*',
            'error_count_*',
            'rate_limit_*'
        ];

        foreach ($patterns as $pattern) {
            $keys = $this->getCacheKeysByPattern($pattern);

            foreach ($keys as $key) {
                $keyData = Cache::get($key);

                if (is_array($keyData) && isset($keyData['timestamp'])) {
                    $keyTime = Carbon::parse($keyData['timestamp']);

                    if ($keyTime->lt($cutoffDate)) {
                        $this->line("  🔑 Deleting cache key: {$key}");

                        if (!$dryRun) {
                            Cache::forget($key);
                        }

                        $deletedKeys++;
                        $freedMemory += strlen(serialize($keyData));
                    }
                }
            }
        }

        $this->line("  ✅ Deleted {$deletedKeys} cache keys, freed ~" . $this->formatBytes($freedMemory));

        return [
            'deleted_keys' => $deletedKeys,
            'freed_memory' => $freedMemory
        ];
    }

    /**
     * Nettoyer les métriques de base de données
     */
    private function cleanupDatabaseMetrics(Carbon $cutoffDate, bool $dryRun): array
    {
        $this->info('🗃️ Cleaning up database metrics...');

        $deletedRecords = 0;

        // Tables à nettoyer (si elles existent)
        $tables = [
            'monitoring_metrics' => 'created_at',
            'monitoring_alerts' => 'created_at',
            'monitoring_logs' => 'timestamp',
            'system_events' => 'created_at'
        ];

        foreach ($tables as $table => $dateColumn) {
            try {
                // Vérifier si la table existe
                if (!$this->tableExists($table)) {
                    continue;
                }

                $query = DB::table($table)->where($dateColumn, '<', $cutoffDate);
                $count = $query->count();

                if ($count > 0) {
                    $this->line("  📊 Table {$table}: {$count} old records");

                    if (!$dryRun) {
                        $deleted = $query->delete();
                        $deletedRecords += $deleted;
                    } else {
                        $deletedRecords += $count;
                    }
                }
            } catch (\Exception $e) {
                $this->warn("  ⚠️ Could not clean table {$table}: " . $e->getMessage());
            }
        }

        $this->line("  ✅ Deleted {$deletedRecords} database records");

        return [
            'deleted_records' => $deletedRecords
        ];
    }

    /**
     * Afficher le résumé du nettoyage
     */
    private function displayCleanupSummary(array $logStats, array $cacheStats, array $dbStats): void
    {
        $this->line('');
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 CLEANUP SUMMARY");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line('');

        $this->line("📁 Log Files:");
        $this->line("  • Processed: {$logStats['processed']} files");
        $this->line("  • Deleted: {$logStats['deleted']} files");
        $this->line("  • Space freed: " . $this->formatBytes($logStats['freed_space']));
        $this->line('');

        $this->line("🗄️ Cache:");
        $this->line("  • Deleted keys: {$cacheStats['deleted_keys']}");
        $this->line("  • Memory freed: ~" . $this->formatBytes($cacheStats['freed_memory']));
        $this->line('');

        $this->line("🗃️ Database:");
        $this->line("  • Deleted records: {$dbStats['deleted_records']}");
        $this->line('');

        $totalFreed = $logStats['freed_space'] + $cacheStats['freed_memory'];
        $this->info("💾 Total space freed: " . $this->formatBytes($totalFreed));
    }

    /**
     * Enregistrer l'événement de nettoyage
     */
    private function logCleanupEvent(array $logStats, array $cacheStats, array $dbStats): void
    {
        $cleanupData = [
            'files_deleted' => $logStats['deleted'],
            'space_freed_bytes' => $logStats['freed_space'],
            'cache_keys_deleted' => $cacheStats['deleted_keys'],
            'db_records_deleted' => $dbStats['deleted_records'],
            'cleanup_timestamp' => Carbon::now()->toISOString()
        ];

        // Log l'événement
        Log::channel('monitoring')->info('Log cleanup completed', $cleanupData);
    }

    /**
     * Obtenir les clés de cache par pattern
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        // Simulation - dans une vraie implémentation,
        // cela dépend du driver de cache utilisé
        return [];
    }

    /**
     * Vérifier si une table existe
     */
    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Formater les bytes en unité lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $factor), 2) . ' ' . $units[$factor];
    }
}
