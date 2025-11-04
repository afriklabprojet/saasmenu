<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheOptimizationService;
use Illuminate\Support\Facades\DB;

class CacheWarmupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:warmup
                           {--vendor= : ID du vendor spécifique à préchauffer}
                           {--all : Préchauffer le cache pour tous les vendors}';

    /**
     * The console command description.
     */
    protected $description = 'Préchauffage intelligent du cache pour améliorer les performances';

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
        $this->info('🔥 Début du préchauffage du cache...');

        $vendorId = $this->option('vendor');
        $all = $this->option('all');

        if ($vendorId) {
            $this->warmupSingleVendor((int) $vendorId);
        } elseif ($all) {
            $this->warmupAllVendors();
        } else {
            $this->warmupSystemCache();
        }

        $this->info('✅ Préchauffage du cache terminé avec succès !');
        return Command::SUCCESS;
    }

    private function warmupSingleVendor(int $vendorId): void
    {
        $this->info("🔄 Préchauffage du cache pour le vendor {$vendorId}...");

        $startTime = microtime(true);
        $this->cacheService->warmupVendorCache($vendorId);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->info("✅ Cache vendor {$vendorId} préchauffé en {$duration}ms");
    }

    private function warmupAllVendors(): void
    {
        $vendors = DB::table('users')
            ->where('type', 2)
            ->where('is_available', 1)
            ->pluck('id');

        $this->info("🔄 Préchauffage du cache pour {$vendors->count()} vendors...");

        $bar = $this->output->createProgressBar($vendors->count());
        $bar->start();

        $totalTime = 0;
        foreach ($vendors as $vendorId) {
            $startTime = microtime(true);
            $this->cacheService->warmupVendorCache($vendorId);
            $duration = microtime(true) - $startTime;
            $totalTime += $duration;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Tous les caches préchauffés en " . round($totalTime, 2) . "s");
    }

    private function warmupSystemCache(): void
    {
        $this->info("🔄 Préchauffage du cache système...");

        $startTime = microtime(true);
        $this->cacheService->getSystemSettings();
        $this->cacheService->getLanguages();
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->info("✅ Cache système préchauffé en {$duration}ms");
    }
}
