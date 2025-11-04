<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheOptimizationService;

class CacheStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:stats';

    /**
     * The console command description.
     */
    protected $description = 'Affiche les statistiques d\'utilisation du cache';

    protected CacheOptimizationService $cacheService;

    public function __construct(CacheOptimizationService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Statistiques du Cache RestroSaaS');
        $this->line('=====================================');

        $stats = $this->cacheService->getCacheStats();

        $this->table([
            'Paramètre', 'Valeur'
        ], [
            ['Driver de cache', $stats['cache_driver']],
            ['Cache activé', $stats['cache_enabled'] ? '✅ Oui' : '❌ Non'],
            ['Préfixe', $stats['cache_prefix'] ?: 'Aucun'],
            ['Timestamp', $stats['timestamp']],
        ]);

        // Test de performance du cache
        $this->testCachePerformance();

        return Command::SUCCESS;
    }

    private function testCachePerformance(): void
    {
        $this->info('⚡ Test de Performance du Cache');
        $this->line('================================');

        // Test d'écriture
        $writeStart = microtime(true);
        cache(['test_key' => 'test_value'], 60);
        $writeTime = round((microtime(true) - $writeStart) * 1000, 2);

        // Test de lecture
        $readStart = microtime(true);
        $value = cache('test_key');
        $readTime = round((microtime(true) - $readStart) * 1000, 2);

        // Nettoyage
        cache()->forget('test_key');

        $this->table([
            'Opération', 'Temps (ms)', 'Status'
        ], [
            ['Écriture cache', $writeTime, $writeTime < 10 ? '✅ Rapide' : '⚠️ Lent'],
            ['Lecture cache', $readTime, $readTime < 5 ? '✅ Rapide' : '⚠️ Lent'],
            ['Intégrité', $value === 'test_value' ? '✅ OK' : '❌ Erreur', ''],
        ]);

        if ($writeTime > 10 || $readTime > 5) {
            $this->warn('⚠️  Performance du cache sous-optimale. Considérez Redis ou Memcached.');
        } else {
            $this->info('✅ Performance du cache excellente !');
        }
    }
}
