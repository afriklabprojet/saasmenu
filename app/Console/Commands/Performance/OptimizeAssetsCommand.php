<?php

namespace App\Console\Commands\Performance;

use Illuminate\Console\Command;
use App\Services\Performance\CDNOptimizationService;
use App\Services\Performance\CoreWebVitalsService;
use Illuminate\Support\Facades\File;

/**
 * Commande d'optimisation des assets front-end
 */
class OptimizeAssetsCommand extends Command
{
    protected $signature = 'optimize:assets
                           {--force : Force re-optimization of existing files}
                           {--images : Optimize images only}
                           {--css : Optimize CSS only}
                           {--js : Optimize JavaScript only}
                           {--bundles : Create bundles only}
                           {--stats : Show optimization statistics}';

    protected $description = 'Optimize front-end assets for performance (CDN, compression, bundling)';

    private $cdnService;
    private $webVitalsService;

    public function __construct(
        CDNOptimizationService $cdnService,
        CoreWebVitalsService $webVitalsService
    ) {
        parent::__construct();
        $this->cdnService = $cdnService;
        $this->webVitalsService = $webVitalsService;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting RestroSaaS Asset Optimization');
        $this->info('================================================');

        $startTime = microtime(true);
        $results = [];

        try {
            // Stats seulement
            if ($this->option('stats')) {
                return $this->showStats();
            }

            // Optimisations sélectives
            if ($this->option('images')) {
                $results['images'] = $this->optimizeImages();
            } elseif ($this->option('css')) {
                $results['css'] = $this->optimizeCSS();
            } elseif ($this->option('js')) {
                $results['js'] = $this->optimizeJavaScript();
            } elseif ($this->option('bundles')) {
                $results['bundles'] = $this->createBundles();
            } else {
                // Optimisation complète
                $results = $this->performFullOptimization();
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->displayResults($results, $duration);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Optimization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Optimisation complète
     */
    private function performFullOptimization(): array
    {
        $results = [];

        $this->info('📸 Optimizing images...');
        $results['images'] = $this->optimizeImages();

        $this->info('🎨 Optimizing CSS...');
        $results['css'] = $this->optimizeCSS();

        $this->info('⚡ Optimizing JavaScript...');
        $results['js'] = $this->optimizeJavaScript();

        $this->info('📦 Creating bundles...');
        $results['bundles'] = $this->createBundles();

        $this->info('🔧 Generating critical CSS...');
        $results['critical_css'] = $this->generateCriticalCSS();

        $this->info('🔄 Warming up cache...');
        $results['cache'] = $this->warmupCache();

        return $results;
    }

    /**
     * Optimise les images
     */
    private function optimizeImages(): array
    {
        $bar = $this->output->createProgressBar();
        $bar->setFormat('verbose');
        $bar->start();

        // Optimiser les images publiques
        $publicResults = $this->cdnService->optimizeImages('public');
        $bar->advance();

        // Optimiser les images des assets admin
        $adminPath = 'public/admin-assets/images';
        if (File::exists(storage_path('app/' . $adminPath))) {
            $adminResults = $this->cdnService->optimizeImages($adminPath);
        } else {
            $adminResults = ['processed' => 0, 'optimized' => 0, 'errors' => 0, 'size_saved' => 0];
        }
        $bar->advance();

        $bar->finish();
        $this->newLine();

        return [
            'public' => $publicResults,
            'admin' => $adminResults,
            'total_processed' => $publicResults['processed'] + $adminResults['processed'],
            'total_optimized' => $publicResults['optimized'] + $adminResults['optimized'],
            'total_size_saved' => $publicResults['size_saved'] + $adminResults['size_saved']
        ];
    }

    /**
     * Optimise les fichiers CSS
     */
    private function optimizeCSS(): array
    {
        $cssFiles = $this->findCSSFiles();
        $optimized = 0;
        $totalSizeSaved = 0;

        $bar = $this->output->createProgressBar(count($cssFiles));
        $bar->setFormat('verbose');
        $bar->start();

        foreach ($cssFiles as $cssFile) {
            $result = $this->optimizeSingleCSS($cssFile);
            if ($result) {
                $optimized++;
                $totalSizeSaved += $result['size_saved'];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return [
            'files_processed' => count($cssFiles),
            'files_optimized' => $optimized,
            'total_size_saved' => $totalSizeSaved
        ];
    }

    /**
     * Optimise un fichier CSS
     */
    private function optimizeSingleCSS(string $cssFile): ?array
    {
        try {
            $originalSize = filesize($cssFile);
            $content = file_get_contents($cssFile);

            $minified = $this->cdnService->minifyCSS($content);

            // Sauvegarder seulement si force ou si pas déjà optimisé
            if ($this->option('force') || strlen($minified) < $originalSize) {
                file_put_contents($cssFile, $minified);
                $newSize = filesize($cssFile);

                return [
                    'original_size' => $originalSize,
                    'new_size' => $newSize,
                    'size_saved' => $originalSize - $newSize
                ];
            }

            return null;

        } catch (\Exception $e) {
            $this->warn("Failed to optimize CSS: {$cssFile}");
            return null;
        }
    }

    /**
     * Optimise les fichiers JavaScript
     */
    private function optimizeJavaScript(): array
    {
        $jsFiles = $this->findJSFiles();
        $optimized = 0;
        $totalSizeSaved = 0;

        $bar = $this->output->createProgressBar(count($jsFiles));
        $bar->setFormat('verbose');
        $bar->start();

        foreach ($jsFiles as $jsFile) {
            $result = $this->optimizeSingleJS($jsFile);
            if ($result) {
                $optimized++;
                $totalSizeSaved += $result['size_saved'];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return [
            'files_processed' => count($jsFiles),
            'files_optimized' => $optimized,
            'total_size_saved' => $totalSizeSaved
        ];
    }

    /**
     * Optimise un fichier JavaScript
     */
    private function optimizeSingleJS(string $jsFile): ?array
    {
        try {
            $originalSize = filesize($jsFile);
            $content = file_get_contents($jsFile);

            $minified = $this->cdnService->minifyJS($content);

            if ($this->option('force') || strlen($minified) < $originalSize) {
                file_put_contents($jsFile, $minified);
                $newSize = filesize($jsFile);

                return [
                    'original_size' => $originalSize,
                    'new_size' => $newSize,
                    'size_saved' => $originalSize - $newSize
                ];
            }

            return null;

        } catch (\Exception $e) {
            $this->warn("Failed to optimize JS: {$jsFile}");
            return null;
        }
    }

    /**
     * Crée les bundles CSS et JS
     */
    private function createBundles(): array
    {
        $bundles = [
            'admin' => [
                'css' => [
                    'admin-assets/css/bootstrap.min.css',
                    'admin-assets/css/style.css'
                ],
                'js' => [
                    'admin-assets/js/jquery.min.js',
                    'admin-assets/js/bootstrap.bundle.min.js'
                ]
            ],
            'dashboard' => [
                'css' => [
                    'admin-assets/css/dashboard.css',
                    'admin-assets/css/charts.css'
                ],
                'js' => [
                    'admin-assets/js/chart.min.js',
                    'admin-assets/js/dashboard.js'
                ]
            ]
        ];

        $created = 0;
        foreach ($bundles as $bundleName => $files) {
            if (!empty($files['css'])) {
                $this->cdnService->createCSSBundle($files['css'], $bundleName);
                $created++;
            }

            if (!empty($files['js'])) {
                $this->cdnService->createJSBundle($files['js'], $bundleName);
                $created++;
            }
        }

        return [
            'bundles_created' => $created,
            'bundle_names' => array_keys($bundles)
        ];
    }

    /**
     * Génère le Critical CSS pour les pages principales
     */
    private function generateCriticalCSS(): array
    {
        $pages = ['dashboard', 'orders', 'products', 'analytics'];
        $generated = [];

        foreach ($pages as $page) {
            try {
                $criticalCSS = $this->webVitalsService->generateCriticalCSS($page);
                $generated[$page] = strlen($criticalCSS);
            } catch (\Exception $e) {
                $this->warn("Failed to generate critical CSS for {$page}");
                $generated[$page] = 0;
            }
        }

        return $generated;
    }

    /**
     * Préchauffe le cache des assets
     */
    private function warmupCache(): array
    {
        $warmed = $this->cdnService->warmupAssetCache();

        return [
            'assets_cached' => count($warmed),
            'cache_entries' => $warmed
        ];
    }

    /**
     * Trouve tous les fichiers CSS
     */
    private function findCSSFiles(): array
    {
        $paths = [
            public_path('admin-assets/css'),
            public_path('css')
        ];

        $files = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $files = array_merge($files, glob($path . '/*.css'));
            }
        }

        // Exclure les fichiers déjà minifiés
        return array_filter($files, function ($file) {
            return !str_contains($file, '.min.css');
        });
    }

    /**
     * Trouve tous les fichiers JavaScript
     */
    private function findJSFiles(): array
    {
        $paths = [
            public_path('admin-assets/js'),
            public_path('js')
        ];

        $files = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $files = array_merge($files, glob($path . '/*.js'));
            }
        }

        // Exclure les fichiers déjà minifiés
        return array_filter($files, function ($file) {
            return !str_contains($file, '.min.js');
        });
    }

    /**
     * Affiche les statistiques d'optimisation
     */
    private function showStats(): int
    {
        $stats = $this->cdnService->getPerformanceStats();
        $webVitalsReport = $this->webVitalsService->getPerformanceReport();

        $this->info('📊 RestroSaaS Performance Statistics');
        $this->info('====================================');

        $this->table(['Metric', 'Value'], [
            ['CDN Enabled', $stats['cdn_enabled'] ? '✅ Yes' : '❌ No'],
            ['WebP Enabled', $stats['webp_enabled'] ? '✅ Yes' : '❌ No'],
            ['Compression Quality', $stats['compression_quality'] . '%'],
            ['Cache Entries', $stats['cache_entries']],
            ['Optimized Images', $stats['optimized_images']],
            ['Bundle Files', $stats['bundle_files']],
            ['Critical CSS Size', number_format($webVitalsReport['critical_css_size']) . ' bytes'],
            ['Resource Hints', $webVitalsReport['resource_hints_count']],
        ]);

        $this->info('🎯 Web Vitals Targets');
        foreach ($webVitalsReport['targets'] as $metric => $target) {
            $this->line("  {$metric}: {$target}");
        }

        return Command::SUCCESS;
    }

    /**
     * Affiche les résultats de l'optimisation
     */
    private function displayResults(array $results, float $duration): void
    {
        $this->newLine();
        $this->info('✅ Optimization completed in ' . $duration . 'ms');
        $this->info('==========================================');

        foreach ($results as $type => $data) {
            $this->displayTypeResults($type, $data);
        }

        // Calculer les gains totaux
        $totalSizeSaved = 0;
        if (isset($results['images']['total_size_saved'])) {
            $totalSizeSaved += $results['images']['total_size_saved'];
        }
        if (isset($results['css']['total_size_saved'])) {
            $totalSizeSaved += $results['css']['total_size_saved'];
        }
        if (isset($results['js']['total_size_saved'])) {
            $totalSizeSaved += $results['js']['total_size_saved'];
        }

        if ($totalSizeSaved > 0) {
            $this->info('💾 Total size saved: ' . $this->formatBytes($totalSizeSaved));
        }

        $this->info('🎉 Front-end performance optimization complete!');
    }

    /**
     * Affiche les résultats par type
     */
    private function displayTypeResults(string $type, array $data): void
    {
        switch ($type) {
            case 'images':
                $this->line("📸 Images: {$data['total_optimized']}/{$data['total_processed']} optimized");
                if ($data['total_size_saved'] > 0) {
                    $this->line("   Size saved: " . $this->formatBytes($data['total_size_saved']));
                }
                break;

            case 'css':
                $this->line("🎨 CSS: {$data['files_optimized']}/{$data['files_processed']} optimized");
                if ($data['total_size_saved'] > 0) {
                    $this->line("   Size saved: " . $this->formatBytes($data['total_size_saved']));
                }
                break;

            case 'js':
                $this->line("⚡ JavaScript: {$data['files_optimized']}/{$data['files_processed']} optimized");
                if ($data['total_size_saved'] > 0) {
                    $this->line("   Size saved: " . $this->formatBytes($data['total_size_saved']));
                }
                break;

            case 'bundles':
                $this->line("📦 Bundles: {$data['bundles_created']} created");
                $this->line("   Names: " . implode(', ', $data['bundle_names']));
                break;

            case 'critical_css':
                $total = array_sum($data);
                $this->line("🔧 Critical CSS: " . count($data) . " pages, " . $this->formatBytes($total));
                break;

            case 'cache':
                $this->line("🔄 Cache: {$data['assets_cached']} assets preloaded");
                break;
        }
    }

    /**
     * Formate les bytes en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
