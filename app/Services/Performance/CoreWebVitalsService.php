<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

/**
 * Core Web Vitals Optimization Service
 * Optimise LCP, FID, CLS pour améliorer les métriques performance
 */
class CoreWebVitalsService
{
    private $criticalCSSThreshold = 14336; // 14KB
    private $resourceHints = [];
    private $preloadResources = [];

    /**
     * Génère le Critical CSS pour une page
     */
    public function generateCriticalCSS(string $pageName, ?string $cssContent = null): string
    {
        $cacheKey = "critical_css_{$pageName}";

        return Cache::remember($cacheKey, 3600, function () use ($pageName, $cssContent) {
            if (!$cssContent) {
                $cssContent = $this->extractPageCSS($pageName);
            }

            // Extraire les styles critiques (above the fold)
            $criticalSelectors = $this->getCriticalSelectors($pageName);
            $criticalCSS = $this->extractCriticalStyles($cssContent, $criticalSelectors);

            // Minifier le CSS critique
            $minified = $this->minifyCSS($criticalCSS);

            // Limiter à 14KB pour le Critical CSS
            if (strlen($minified) > $this->criticalCSSThreshold) {
                $minified = substr($minified, 0, $this->criticalCSSThreshold);
                $minified = substr($minified, 0, strrpos($minified, '}') + 1);
            }

            Log::info("Generated critical CSS for {$pageName}: " . strlen($minified) . " bytes");
            return $minified;
        });
    }

    /**
     * Sélecteurs CSS critiques par page
     */
    private function getCriticalSelectors(string $pageName): array
    {
        $selectors = [
            'common' => [
                'body', 'html', '.container', '.row', '.col-*',
                '.navbar', '.header', '.btn', '.btn-primary',
                '.card', '.card-body', '.text-*', '.bg-*',
                '.d-*', '.m-*', '.p-*', '.flex-*'
            ],
            'dashboard' => [
                '.dashboard-header', '.stats-card', '.chart-container',
                '.quick-stats', '.revenue-chart', '.orders-summary'
            ],
            'orders' => [
                '.orders-table', '.order-status', '.order-actions',
                '.filters-section', '.pagination'
            ],
            'products' => [
                '.products-grid', '.product-card', '.product-image',
                '.price-tag', '.category-filter'
            ]
        ];

        $pageSelectors = array_merge(
            $selectors['common'],
            $selectors[$pageName] ?? []
        );

        return $pageSelectors;
    }

    /**
     * Extrait les styles critiques du CSS complet
     */
    private function extractCriticalStyles(string $cssContent, array $selectors): string
    {
        $criticalCSS = '';

        foreach ($selectors as $selector) {
            // Pattern pour extraire les règles CSS
            $pattern = '/(' . preg_quote($selector, '/') . '.*?{[^}]*})/s';

            // Gestion des sélecteurs avec wildcards
            if (strpos($selector, '*') !== false) {
                $pattern = '/' . str_replace('\*', '[^{]*', preg_quote($selector, '/')) . '{[^}]*}/';
            }

            preg_match_all($pattern, $cssContent, $matches);

            if (!empty($matches[0])) {
                $criticalCSS .= implode("\n", $matches[0]) . "\n";
            }
        }

        return $criticalCSS;
    }

    /**
     * Génère les Resource Hints pour optimiser le chargement
     */
    public function generateResourceHints(string $pageName): array
    {
        $hints = [
            'preconnect' => [
                'https://fonts.googleapis.com',
                'https://fonts.gstatic.com',
                'https://cdn.jsdelivr.net'
            ],
            'dns-prefetch' => [
                '//www.google-analytics.com',
                '//www.googletagmanager.com'
            ],
            'preload' => $this->getPreloadResources($pageName),
            'prefetch' => $this->getPrefetchResources($pageName)
        ];

        return $hints;
    }

    /**
     * Ressources à précharger par page
     */
    private function getPreloadResources(string $pageName): array
    {
        $common = [
            [
                'href' => '/admin-assets/css/bootstrap.min.css',
                'as' => 'style'
            ],
            [
                'href' => '/admin-assets/css/style.css',
                'as' => 'style'
            ],
            [
                'href' => '/admin-assets/js/jquery.min.js',
                'as' => 'script'
            ]
        ];

        $pageSpecific = [
            'dashboard' => [
                [
                    'href' => '/admin-assets/js/chart.min.js',
                    'as' => 'script'
                ],
                [
                    'href' => '/admin-assets/js/dashboard.js',
                    'as' => 'script'
                ]
            ],
            'orders' => [
                [
                    'href' => '/admin-assets/js/datatables.min.js',
                    'as' => 'script'
                ]
            ]
        ];

        return array_merge($common, $pageSpecific[$pageName] ?? []);
    }

    /**
     * Ressources à précharger en arrière-plan
     */
    private function getPrefetchResources(string $pageName): array
    {
        $routes = [
            'dashboard' => ['/admin/orders', '/admin/products'],
            'orders' => ['/admin/dashboard', '/admin/analytics'],
            'products' => ['/admin/orders', '/admin/categories']
        ];

        return $routes[$pageName] ?? [];
    }

    /**
     * Optimise les images pour LCP (Largest Contentful Paint)
     */
    public function optimizeImagesForLCP(string $content): string
    {
        // Ajouter loading="lazy" aux images non-critiques
        $content = preg_replace_callback(
            '/<img([^>]+)>/i',
            function ($matches) {
                $imgTag = $matches[0];

                // Skip si déjà lazy loading ou si critique
                if (strpos($imgTag, 'loading=') !== false ||
                    strpos($imgTag, 'data-critical') !== false) {
                    return $imgTag;
                }

                // Ajouter lazy loading
                return str_replace('<img', '<img loading="lazy"', $imgTag);
            },
            $content
        );

        // Optimiser les images critiques avec fetchpriority
        $content = preg_replace(
            '/<img([^>]+)data-critical([^>]*)>/i',
            '<img$1fetchpriority="high"$2>',
            $content
        );

        return $content;
    }

    /**
     * Génère le JavaScript d'optimisation CLS (Cumulative Layout Shift)
     */
    public function generateCLSOptimization(): string
    {
        return <<<'JS'
// CLS Optimization - Prevent layout shifts
(function() {
    'use strict';

    // Reserve space for images before loading
    function reserveImageSpace() {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            if (!img.style.aspectRatio && img.dataset.width && img.dataset.height) {
                img.style.aspectRatio = `${img.dataset.width} / ${img.dataset.height}`;
            }
        });
    }

    // Reserve space for dynamic content
    function reserveDynamicSpace() {
        const containers = document.querySelectorAll('[data-min-height]');
        containers.forEach(container => {
            if (!container.style.minHeight) {
                container.style.minHeight = container.dataset.minHeight + 'px';
            }
        });
    }

    // Prevent font swap layout shift
    function optimizeFonts() {
        const style = document.createElement('style');
        style.textContent = `
            @font-face {
                font-family: system-ui;
                font-display: swap;
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize optimizations
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            reserveImageSpace();
            reserveDynamicSpace();
            optimizeFonts();
        });
    } else {
        reserveImageSpace();
        reserveDynamicSpace();
        optimizeFonts();
    }
})();
JS;
    }

    /**
     * Optimise le JavaScript pour FID (First Input Delay)
     */
    public function optimizeJavaScriptForFID(): string
    {
        return <<<'JS'
// FID Optimization - Reduce main thread blocking
(function() {
    'use strict';

    // Defer non-critical JavaScript
    function deferNonCriticalJS() {
        const scripts = document.querySelectorAll('script[data-defer]');
        scripts.forEach(script => {
            if (script.src) {
                const newScript = document.createElement('script');
                newScript.src = script.src;
                newScript.async = true;
                script.remove();
                document.body.appendChild(newScript);
            }
        });
    }

    // Break up long tasks
    function breakUpLongTasks() {
        const scheduler = window.scheduler || {
            postTask: (callback) => setTimeout(callback, 0)
        };

        return {
            yieldToMain: () => {
                return new Promise(resolve => {
                    scheduler.postTask(resolve);
                });
            }
        };
    }

    // Lazy load heavy components
    function lazyLoadComponents() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const component = entry.target;
                    const componentName = component.dataset.component;

                    if (componentName && window[componentName]) {
                        window[componentName].init(component);
                        observer.unobserve(component);
                    }
                }
            });
        });

        document.querySelectorAll('[data-component]').forEach(el => {
            observer.observe(el);
        });
    }

    // Initialize FID optimizations
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            deferNonCriticalJS();
            lazyLoadComponents();
        });
    } else {
        deferNonCriticalJS();
        lazyLoadComponents();
    }

    // Export utilities
    window.WebVitalsOptimizer = {
        breakUpLongTasks: breakUpLongTasks()
    };
})();
JS;
    }

    /**
     * Injecte les optimisations dans le HTML
     */
    public function injectOptimizations(string $html, string $pageName): string
    {
        // Injecter Critical CSS
        $criticalCSS = $this->generateCriticalCSS($pageName);
        $html = str_replace(
            '</head>',
            "<style>{$criticalCSS}</style>\n</head>",
            $html
        );

        // Injecter Resource Hints
        $hints = $this->generateResourceHints($pageName);
        $hintsHTML = $this->generateResourceHintsHTML($hints);
        $html = str_replace(
            '</head>',
            "{$hintsHTML}\n</head>",
            $html
        );

        // Optimiser les images
        $html = $this->optimizeImagesForLCP($html);

        // Injecter les scripts d'optimisation
        $clsScript = $this->generateCLSOptimization();
        $fidScript = $this->optimizeJavaScriptForFID();

        $html = str_replace(
            '</body>',
            "<script>{$clsScript}</script>\n<script>{$fidScript}</script>\n</body>",
            $html
        );

        return $html;
    }

    /**
     * Génère le HTML pour les Resource Hints
     */
    private function generateResourceHintsHTML(array $hints): string
    {
        $html = '';

        foreach ($hints['preconnect'] as $url) {
            $html .= "<link rel=\"preconnect\" href=\"{$url}\">\n";
        }

        foreach ($hints['dns-prefetch'] as $url) {
            $html .= "<link rel=\"dns-prefetch\" href=\"{$url}\">\n";
        }

        foreach ($hints['preload'] as $resource) {
            $html .= "<link rel=\"preload\" href=\"{$resource['href']}\" as=\"{$resource['as']}\">\n";
        }

        foreach ($hints['prefetch'] as $url) {
            $html .= "<link rel=\"prefetch\" href=\"{$url}\">\n";
        }

        return $html;
    }

    /**
     * Extrait le CSS spécifique à une page
     */
    private function extractPageCSS(string $pageName): string
    {
        $cssFiles = [
            'admin-assets/css/bootstrap.min.css',
            'admin-assets/css/style.css'
        ];

        $cssContent = '';
        foreach ($cssFiles as $cssFile) {
            $path = public_path($cssFile);
            if (file_exists($path)) {
                $cssContent .= file_get_contents($path) . "\n";
            }
        }

        return $cssContent;
    }

    /**
     * Minifie le CSS
     */
    private function minifyCSS(string $css): string
    {
        // Supprimer commentaires
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Supprimer espaces inutiles
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace([' {', '{ ', ' }', '} '], ['{', '{', '}', '}'], $css);

        return trim($css);
    }

    /**
     * Mesure les Core Web Vitals
     */
    public function measureWebVitals(): string
    {
        return <<<'JS'
// Web Vitals Measurement
(function() {
    'use strict';

    let vitals = {
        lcp: null,
        fid: null,
        cls: null
    };

    // Measure LCP
    if ('PerformanceObserver' in window) {
        const lcpObserver = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const lastEntry = entries[entries.length - 1];
            vitals.lcp = lastEntry.startTime;
        });
        lcpObserver.observe({entryTypes: ['largest-contentful-paint']});

        // Measure FID
        const fidObserver = new PerformanceObserver((list) => {
            list.getEntries().forEach((entry) => {
                vitals.fid = entry.processingStart - entry.startTime;
            });
        });
        fidObserver.observe({entryTypes: ['first-input']});

        // Measure CLS
        let clsValue = 0;
        const clsObserver = new PerformanceObserver((list) => {
            list.getEntries().forEach((entry) => {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            });
            vitals.cls = clsValue;
        });
        clsObserver.observe({entryTypes: ['layout-shift']});
    }

    // Send vitals to analytics
    window.addEventListener('beforeunload', () => {
        if (navigator.sendBeacon && vitals.lcp) {
            navigator.sendBeacon('/api/analytics/web-vitals', JSON.stringify(vitals));
        }
    });

    // Expose vitals for debugging
    window.webVitals = vitals;
})();
JS;
    }

    /**
     * Rapport de performance Web Vitals
     */
    public function getPerformanceReport(): array
    {
        return [
            'critical_css_size' => strlen($this->generateCriticalCSS('dashboard')),
            'resource_hints_count' => count($this->generateResourceHints('dashboard')['preload']),
            'optimizations_active' => [
                'critical_css' => true,
                'lazy_loading' => true,
                'resource_hints' => true,
                'cls_prevention' => true,
                'fid_optimization' => true
            ],
            'targets' => [
                'lcp' => '< 2.5s',
                'fid' => '< 100ms',
                'cls' => '< 0.1'
            ]
        ];
    }
}
