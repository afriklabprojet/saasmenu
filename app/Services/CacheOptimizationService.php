<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

/**
 * Service de cache pour optimiser les performances
 * Implémente une stratégie de cache multicouche pour les requêtes fréquentes
 */
class CacheOptimizationService
{
    // TTL par type de données (en secondes)
    const CACHE_TTL = [
        'categories' => 3600,      // 1 heure
        'products' => 1800,        // 30 minutes
        'vendor_data' => 7200,     // 2 heures
        'settings' => 14400,       // 4 heures
        'static_content' => 86400, // 24 heures
    ];

    /**
     * Cache des catégories avec produits pour un vendor
     */
    public function getCategoriesWithProducts(int $vendorId): array
    {
        $cacheKey = "vendor_{$vendorId}_categories_with_products";

        return Cache::remember($cacheKey, self::CACHE_TTL['categories'], function() use ($vendorId) {
            return Category::with(['products' => function($query) {
                    $query->where('is_available', 1)
                          ->orderBy('reorder_id')
                          ->select(['id', 'category_id', 'name', 'description', 'price', 'image', 'is_available']);
                }])
                ->where('vendor_id', $vendorId)
                ->where('is_available', 1)
                ->orderBy('reorder_id')
                ->get()
                ->toArray();
        });
    }

    /**
     * Cache des produits populaires par vendor
     */
    public function getPopularProducts(int $vendorId, int $limit = 10): array
    {
        $cacheKey = "vendor_{$vendorId}_popular_products_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL['products'], function() use ($vendorId, $limit) {
            return DB::table('products')
                ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                ->select([
                    'products.id',
                    'products.name',
                    'products.price',
                    'products.image',
                    'products.description',
                    DB::raw('COUNT(order_details.id) as order_count'),
                    DB::raw('SUM(order_details.qty) as total_sold')
                ])
                ->where('products.vendor_id', $vendorId)
                ->where('products.is_available', 1)
                ->groupBy(['products.id', 'products.name', 'products.price', 'products.image', 'products.description'])
                ->orderByDesc('order_count')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Cache des informations vendor
     */
    public function getVendorInfo(int $vendorId): ?array
    {
        $cacheKey = "vendor_{$vendorId}_info";

        return Cache::remember($cacheKey, self::CACHE_TTL['vendor_data'], function() use ($vendorId) {
            $vendor = User::where('id', $vendorId)
                ->where('type', 2)
                ->select([
                    'id', 'name', 'email', 'mobile', 'image',
                    'description', 'address', 'is_available',
                    'delivery_charge', 'min_order_amount'
                ])
                ->first();

            return $vendor ? $vendor->toArray() : null;
        });
    }

    /**
     * Cache des statistiques vendor pour dashboard
     */
    public function getVendorDashboardStats(int $vendorId): array
    {
        $cacheKey = "vendor_{$vendorId}_dashboard_stats";

        return Cache::remember($cacheKey, 900, function() use ($vendorId) { // 15 minutes
            return [
                'total_orders' => DB::table('orders')->where('vendor_id', $vendorId)->count(),
                'pending_orders' => DB::table('orders')->where('vendor_id', $vendorId)->where('status', 1)->count(),
                'today_orders' => DB::table('orders')->where('vendor_id', $vendorId)->whereDate('created_at', today())->count(),
                'total_revenue' => DB::table('orders')->where('vendor_id', $vendorId)->sum('grand_total'),
                'total_products' => DB::table('products')->where('vendor_id', $vendorId)->where('is_available', 1)->count(),
                'total_categories' => DB::table('categories')->where('vendor_id', $vendorId)->where('is_available', 1)->count(),
            ];
        });
    }

    /**
     * Cache des paramètres système pour un vendor
     */
    public function getVendorSettings(int $vendorId): ?array
    {
        $cacheKey = "vendor_{$vendorId}_settings";

        return Cache::remember($cacheKey, self::CACHE_TTL['settings'], function() use ($vendorId) {
            $settings = DB::table('settings')
                ->where('vendor_id', $vendorId)
                ->first();

            return $settings ? (array) $settings : null;
        });
    }

    /**
     * Cache des paramètres système globaux (vendor_id = 1)
     */
    public function getSystemSettings(): ?array
    {
        return $this->getVendorSettings(1); // Admin settings
    }    /**
     * Cache du contenu statique (langues, etc.)
     */
    public function getLanguages(): array
    {
        $cacheKey = "system_languages";

        return Cache::remember($cacheKey, self::CACHE_TTL['static_content'], function() {
            return DB::table('languages')
                ->where('is_available', 1)
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Cache des codes promo actifs pour un vendor
     */
    public function getActivePromoCodes(int $vendorId): array
    {
        $cacheKey = "vendor_{$vendorId}_active_promocodes";

        return Cache::remember($cacheKey, 1800, function() use ($vendorId) { // 30 minutes
            return DB::table('promocodes')
                ->where('vendor_id', $vendorId)
                ->where('is_available', 1)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get()
                ->toArray();
        });
    }

    /**
     * Invalidation intelligente du cache
     */
    public function invalidateVendorCache(int $vendorId, array $types = ['all']): void
    {
        if (in_array('all', $types) || in_array('categories', $types)) {
            Cache::forget("vendor_{$vendorId}_categories_with_products");
        }

        if (in_array('all', $types) || in_array('products', $types)) {
            // Invalider tous les caches de produits pour ce vendor
            for ($limit = 5; $limit <= 50; $limit += 5) {
                Cache::forget("vendor_{$vendorId}_popular_products_{$limit}");
            }
        }

        if (in_array('all', $types) || in_array('vendor', $types)) {
            Cache::forget("vendor_{$vendorId}_info");
            Cache::forget("vendor_{$vendorId}_dashboard_stats");
        }

        if (in_array('all', $types) || in_array('promocodes', $types)) {
            Cache::forget("vendor_{$vendorId}_active_promocodes");
        }
    }

    /**
     * Invalidation globale du cache système
     */
    public function invalidateSystemCache(): void
    {
        Cache::forget("system_settings");
        Cache::forget("system_languages");
    }

    /**
     * Préchauffage du cache pour un vendor
     */
    public function warmupVendorCache(int $vendorId): void
    {
        // Préchauffer les données importantes
        $this->getCategoriesWithProducts($vendorId);
        $this->getPopularProducts($vendorId);
        $this->getVendorInfo($vendorId);
        $this->getVendorDashboardStats($vendorId);
        $this->getActivePromoCodes($vendorId);
    }

    /**
     * Statistiques d'utilisation du cache
     */
    public function getCacheStats(): array
    {
        // Note: Ces statistiques dépendent du driver de cache utilisé
        // Pour Redis, on pourrait obtenir des stats plus détaillées

        return [
            'cache_driver' => config('cache.default'),
            'cache_enabled' => config('cache.enabled', true),
            'cache_prefix' => config('cache.prefix'),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Nettoyage du cache expiré
     */
    public function cleanupExpiredCache(): int
    {
        $cleaned = 0;

        // Pour Redis ou Memcached, le nettoyage est automatique
        // Pour file cache, on pourrait implémenter un nettoyage manuel

        if (config('cache.default') === 'file') {
            // Logique de nettoyage des fichiers expirés
            $cacheDir = storage_path('framework/cache/data');
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < (time() - 86400)) { // 24h
                        unlink($file);
                        $cleaned++;
                    }
                }
            }
        }

        return $cleaned;
    }
}
