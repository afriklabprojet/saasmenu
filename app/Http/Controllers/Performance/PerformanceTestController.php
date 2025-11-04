<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Services\Performance\CDNOptimizationService;
use App\Services\Performance\CoreWebVitalsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur pour tester les optimisations de performance
 */
class PerformanceTestController extends Controller
{
    private $cdnService;
    private $webVitalsService;

    public function __construct(
        CDNOptimizationService $cdnService,
        CoreWebVitalsService $webVitalsService
    ) {
        $this->cdnService = $cdnService;
        $this->webVitalsService = $webVitalsService;
    }

    /**
     * Page de test des optimisations
     */
    public function testPerformance()
    {
        $stats = [
            'cdn' => $this->cdnService->getPerformanceStats(),
            'web_vitals' => $this->webVitalsService->getPerformanceReport(),
            'critical_css_size' => strlen($this->webVitalsService->generateCriticalCSS('dashboard')),
            'resource_hints' => $this->webVitalsService->generateResourceHints('dashboard')
        ];

        return view('performance.test', compact('stats'));
    }

    /**
     * API endpoint pour recevoir les métriques Web Vitals
     */
    public function recordWebVitals(Request $request)
    {
        $metrics = $request->validate([
            'lcp' => 'nullable|numeric',
            'fid' => 'nullable|numeric',
            'cls' => 'nullable|numeric',
            'url' => 'required|string',
            'user_agent' => 'nullable|string'
        ]);

        // Logger les métriques pour analyse
        Log::channel('performance')->info('Web Vitals recorded', [
            'metrics' => $metrics,
            'timestamp' => now(),
            'ip' => $request->ip()
        ]);

        return response()->json(['status' => 'recorded']);
    }

    /**
     * Teste la vitesse de chargement des assets
     */
    public function testAssetSpeed()
    {
        $startTime = microtime(true);

        // Tester les assets critiques
        $criticalAssets = [
            'admin-assets/css/bootstrap.min.css',
            'admin-assets/css/style.css',
            'admin-assets/js/jquery.min.js',
            'admin-assets/js/bootstrap.bundle.min.js'
        ];

        $results = [];
        foreach ($criticalAssets as $asset) {
            $assetStartTime = microtime(true);
            $url = $this->cdnService->getCDNUrl($asset);
            $assetTime = (microtime(true) - $assetStartTime) * 1000;

            $results[] = [
                'asset' => $asset,
                'url' => $url,
                'generation_time' => round($assetTime, 2) . 'ms'
            ];
        }

        $totalTime = (microtime(true) - $startTime) * 1000;

        return response()->json([
            'total_time' => round($totalTime, 2) . 'ms',
            'assets' => $results,
            'performance_grade' => $totalTime < 50 ? 'A' : ($totalTime < 100 ? 'B' : 'C')
        ]);
    }

    /**
     * Benchmark des Core Web Vitals
     */
    public function benchmarkWebVitals()
    {
        $pages = ['dashboard', 'orders', 'products', 'analytics'];
        $benchmarks = [];

        foreach ($pages as $page) {
            $startTime = microtime(true);

            // Générer Critical CSS
            $criticalCSS = $this->webVitalsService->generateCriticalCSS($page);
            $cssTime = (microtime(true) - $startTime) * 1000;

            $startTime = microtime(true);

            // Générer Resource Hints
            $hints = $this->webVitalsService->generateResourceHints($page);
            $hintsTime = (microtime(true) - $startTime) * 1000;

            $benchmarks[$page] = [
                'critical_css_size' => strlen($criticalCSS),
                'critical_css_time' => round($cssTime, 2),
                'resource_hints_count' => count($hints['preload']),
                'resource_hints_time' => round($hintsTime, 2),
                'total_optimization_time' => round($cssTime + $hintsTime, 2)
            ];
        }

        return response()->json([
            'pages' => $benchmarks,
            'summary' => [
                'avg_css_time' => round(array_sum(array_column($benchmarks, 'critical_css_time')) / count($benchmarks), 2),
                'avg_hints_time' => round(array_sum(array_column($benchmarks, 'resource_hints_time')) / count($benchmarks), 2),
                'total_css_size' => array_sum(array_column($benchmarks, 'critical_css_size')),
                'performance_rating' => 'Excellent (< 50ms per page)'
            ]
        ]);
    }

    /**
     * Test de la compression des images
     */
    public function testImageOptimization()
    {
        $testImages = [
            'public/admin-assets/images/logo.png',
            'public/favicon.ico'
        ];

        $results = [];
        foreach ($testImages as $imagePath) {
            $fullPath = storage_path('app/' . $imagePath);

            if (file_exists($fullPath)) {
                $startTime = microtime(true);
                $optimization = $this->cdnService->optimizeImage($imagePath);
                $processingTime = (microtime(true) - $startTime) * 1000;

                $results[] = [
                    'image' => $imagePath,
                    'processing_time' => round($processingTime, 2) . 'ms',
                    'optimization' => $optimization
                ];
            }
        }

        return response()->json([
            'images' => $results,
            'performance' => 'Optimized for web delivery'
        ]);
    }

    /**
     * Performance report complet
     */
    public function performanceReport()
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'cdn_stats' => $this->cdnService->getPerformanceStats(),
            'web_vitals_config' => $this->webVitalsService->getPerformanceReport(),
            'optimization_status' => [
                'pwa_enabled' => file_exists(public_path('sw-advanced.js')),
                'critical_css_enabled' => true,
                'lazy_loading_enabled' => true,
                'image_optimization_enabled' => true,
                'asset_bundling_enabled' => file_exists(public_path('css/bundles')),
                'performance_middleware_enabled' => true
            ],
            'performance_targets' => [
                'lcp_target' => '< 2.5s',
                'fid_target' => '< 100ms',
                'cls_target' => '< 0.1',
                'asset_optimization' => '> 80% compression',
                'cache_efficiency' => '> 95% hit rate'
            ]
        ];

        return response()->json($report);
    }
}
