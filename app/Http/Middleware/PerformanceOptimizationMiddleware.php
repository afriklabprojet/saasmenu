<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\Performance\CoreWebVitalsService;
use App\Services\Performance\CDNOptimizationService;

/**
 * Performance Optimization Middleware
 * Optimise automatiquement les réponses pour la performance front-end
 */
class PerformanceOptimizationMiddleware
{
    private $coreWebVitalsService;
    private $cdnService;

    public function __construct(
        CoreWebVitalsService $coreWebVitalsService,
        CDNOptimizationService $cdnService
    ) {
        $this->coreWebVitalsService = $coreWebVitalsService;
        $this->cdnService = $cdnService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Optimiser seulement les réponses HTML des pages admin
        if ($this->shouldOptimize($request, $response)) {
            $content = $response->getContent();
            $pageName = $this->getPageName($request);

            // Appliquer les optimisations
            $optimizedContent = $this->optimizeContent($content, $pageName, $request);

            // Mettre à jour la réponse
            $response->setContent($optimizedContent);

            // Ajouter les headers de performance
            $this->addPerformanceHeaders($response);
        }

        return $response;
    }

    /**
     * Détermine si la réponse doit être optimisée
     */
    private function shouldOptimize(Request $request, Response $response): bool
    {
        // Optimiser seulement les pages HTML des routes admin
        return $response->getStatusCode() === 200 &&
               str_contains($response->headers->get('Content-Type', ''), 'text/html') &&
               str_starts_with($request->path(), 'admin/') &&
               !$request->ajax() &&
               !$request->wantsJson();
    }

    /**
     * Extrait le nom de la page depuis la route
     */
    private function getPageName(Request $request): string
    {
        $path = $request->path();

        // Mapper les routes vers les noms de pages
        $pageMap = [
            'admin/dashboard' => 'dashboard',
            'admin/orders' => 'orders',
            'admin/products' => 'products',
            'admin/analytics' => 'analytics',
            'admin/categories' => 'categories',
            'admin/customers' => 'customers'
        ];

        foreach ($pageMap as $route => $pageName) {
            if (str_starts_with($path, $route)) {
                return $pageName;
            }
        }

        return 'default';
    }

    /**
     * Optimise le contenu HTML
     */
    private function optimizeContent(string $content, string $pageName, Request $request): string
    {
        // 1. Injecter les optimisations Core Web Vitals
        $content = $this->coreWebVitalsService->injectOptimizations($content, $pageName);

        // 2. Optimiser les URLs des assets avec CDN
        $content = $this->optimizeAssetURLs($content);

        // 3. Ajouter la mesure des Web Vitals
        $content = $this->addWebVitalsMeasurement($content);

        // 4. Optimiser la compression
        $content = $this->optimizeHTML($content);

        // 5. Ajouter les scripts de performance
        $content = $this->addPerformanceScripts($content, $pageName);

        return $content;
    }

    /**
     * Optimise les URLs des assets avec CDN
     */
    private function optimizeAssetURLs(string $content): string
    {
        // Remplacer les URLs d'assets par les URLs CDN
        $content = preg_replace_callback(
            '/(?:src|href)=["\']([^"\']+\.(css|js|png|jpg|jpeg|gif|webp|svg))["\']/',
            function ($matches) {
                $assetPath = ltrim($matches[1], '/');
                return str_replace($matches[1], $this->cdnService->getCDNUrl($assetPath), $matches[0]);
            },
            $content
        );

        return $content;
    }

    /**
     * Ajoute la mesure des Web Vitals
     */
    private function addWebVitalsMeasurement(string $content): string
    {
        $vitalsScript = $this->coreWebVitalsService->measureWebVitals();

        return str_replace(
            '</body>',
            "<script>{$vitalsScript}</script>\n</body>",
            $content
        );
    }

    /**
     * Optimise le HTML (minification légère)
     */
    private function optimizeHTML(string $html): string
    {
        // Supprimer les commentaires HTML (mais garder les conditionnels IE)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Supprimer les espaces inutiles entre les balises
        $html = preg_replace('/>\s+</', '><', $html);

        // Supprimer les espaces en début/fin de ligne
        $html = preg_replace('/^\s+|\s+$/m', '', $html);

        // Supprimer les lignes vides multiples
        $html = preg_replace('/\n\s*\n/', "\n", $html);

        return trim($html);
    }

    /**
     * Ajoute les scripts de performance spécifiques à la page
     */
    private function addPerformanceScripts(string $content, string $pageName): string
    {
        $scripts = $this->getPageSpecificScripts($pageName);

        if (!empty($scripts)) {
            $scriptTags = '';
            foreach ($scripts as $script) {
                $scriptTags .= "<script async>{$script}</script>\n";
            }

            $content = str_replace('</body>', "{$scriptTags}</body>", $content);
        }

        return $content;
    }

    /**
     * Scripts de performance spécifiques par page
     */
    private function getPageSpecificScripts(string $pageName): array
    {
        $scripts = [];

        switch ($pageName) {
            case 'dashboard':
                $scripts[] = $this->getDashboardOptimizations();
                break;

            case 'orders':
                $scripts[] = $this->getOrdersOptimizations();
                break;

            case 'products':
                $scripts[] = $this->getProductsOptimizations();
                break;
        }

        return $scripts;
    }

    /**
     * Optimisations spécifiques au dashboard
     */
    private function getDashboardOptimizations(): string
    {
        return <<<'JS'
// Dashboard Performance Optimizations
(function() {
    'use strict';

    // Lazy load charts
    const chartContainers = document.querySelectorAll('[data-chart]');
    const chartObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.loaded) {
                loadChart(entry.target);
                entry.target.dataset.loaded = 'true';
                chartObserver.unobserve(entry.target);
            }
        });
    });

    chartContainers.forEach(container => {
        chartObserver.observe(container);
    });

    function loadChart(container) {
        const chartType = container.dataset.chart;
        const chartData = JSON.parse(container.dataset.chartData || '{}');

        // Simuler le chargement différé des charts
        setTimeout(() => {
            if (window.Chart && window[chartType + 'Chart']) {
                window[chartType + 'Chart'].init(container, chartData);
            }
        }, 100);
    }

    // Optimize real-time updates
    let updateTimeout;
    function throttledUpdate() {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(() => {
            if (document.visibilityState === 'visible') {
                updateDashboardMetrics();
            }
        }, 5000);
    }

    document.addEventListener('visibilitychange', throttledUpdate);
})();
JS;
    }

    /**
     * Optimisations spécifiques aux commandes
     */
    private function getOrdersOptimizations(): string
    {
        return <<<'JS'
// Orders Performance Optimizations
(function() {
    'use strict';

    // Virtual scrolling for large order lists
    const orderTable = document.querySelector('.orders-table tbody');
    if (orderTable && orderTable.children.length > 50) {
        implementVirtualScrolling(orderTable);
    }

    function implementVirtualScrolling(container) {
        const itemHeight = 60; // hauteur estimée d'une ligne
        const visibleItems = Math.ceil(window.innerHeight / itemHeight) + 5;
        let scrollTop = 0;

        const wrapper = document.createElement('div');
        wrapper.style.height = (container.children.length * itemHeight) + 'px';
        wrapper.style.overflow = 'auto';

        container.parentNode.replaceChild(wrapper, container);
        wrapper.appendChild(container);

        function updateVisibleItems() {
            const startIndex = Math.floor(scrollTop / itemHeight);
            const endIndex = Math.min(startIndex + visibleItems, container.children.length);

            for (let i = 0; i < container.children.length; i++) {
                const item = container.children[i];
                if (i >= startIndex && i <= endIndex) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            }
        }

        wrapper.addEventListener('scroll', (e) => {
            scrollTop = e.target.scrollTop;
            requestAnimationFrame(updateVisibleItems);
        });

        updateVisibleItems();
    }

    // Debounce search input
    const searchInput = document.querySelector('#orderSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(e.target.value);
            }, 300);
        });
    }
})();
JS;
    }

    /**
     * Optimisations spécifiques aux produits
     */
    private function getProductsOptimizations(): string
    {
        return <<<'JS'
// Products Performance Optimizations
(function() {
    'use strict';

    // Lazy load product images
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });

    // Optimize product grid scrolling
    const productGrid = document.querySelector('.products-grid');
    if (productGrid) {
        let ticking = false;

        function updateGridLayout() {
            if (!ticking) {
                requestAnimationFrame(() => {
                    // Optimisations de layout si nécessaire
                    ticking = false;
                });
                ticking = true;
            }
        }

        window.addEventListener('scroll', updateGridLayout);
        window.addEventListener('resize', updateGridLayout);
    }
})();
JS;
    }

    /**
     * Ajoute les headers de performance
     */
    private function addPerformanceHeaders(Response $response): void
    {
        // Headers de cache
        $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=86400');

        // Headers de compression
        if (function_exists('gzencode') && !$response->headers->has('Content-Encoding')) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        // Headers de sécurité pour la performance
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Headers pour PWA
        $response->headers->set('Service-Worker-Allowed', '/');

        // Headers pour les métriques
        $response->headers->set('Server-Timing', 'optimization;dur=1.2;desc="Performance middleware"');
    }
}
