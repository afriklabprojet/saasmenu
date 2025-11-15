<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CacheOptimizationService;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;
use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class CacheOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cacheService;
    protected $restaurant;
    protected $category;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheService = app(CacheOptimizationService::class);

        $this->restaurant = Restaurant::factory()->create([
            'restaurant_name' => 'Test Restaurant',
            'is_active' => 1
        ]);

        $this->category = Category::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'name' => 'Test Category',
            'is_available' => 1
        ]);

        $this->item = Item::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'cat_id' => $this->category->id,
            'name' => 'Test Item',
            'is_available' => 1
        ]);
    }

    /**
     * Test de mise en cache des catégories par restaurant
     */
    public function test_cache_vendor_categories()
    {
        $vendorId = $this->restaurant->user_id;
        $cacheKey = "vendor_categories_{$vendorId}";

        // S'assurer que le cache est vide
        Cache::forget($cacheKey);
        $this->assertFalse(Cache::has($cacheKey));

        // Mettre en cache
        $result = $this->cacheService->cacheVendorCategories($vendorId);

        $this->assertTrue($result);
        $this->assertTrue(Cache::has($cacheKey));

        // Vérifier le contenu du cache
        $cachedCategories = Cache::get($cacheKey);
        $this->assertIsArray($cachedCategories);
        $this->assertCount(1, $cachedCategories);
        $this->assertEquals('Test Category', $cachedCategories[0]['name']);
    }

    /**
     * Test de mise en cache des produits par catégorie
     */
    public function test_cache_category_items()
    {
        $categoryId = $this->category->id;
        $cacheKey = "category_items_{$categoryId}";

        Cache::forget($cacheKey);

        $result = $this->cacheService->cacheCategoryItems($categoryId);

        $this->assertTrue($result);
        $this->assertTrue(Cache::has($cacheKey));

        $cachedItems = Cache::get($cacheKey);
        $this->assertIsArray($cachedItems);
        $this->assertCount(1, $cachedItems);
        $this->assertEquals('Test Item', $cachedItems[0]['name']);
    }

    /**
     * Test de mise en cache des données de restaurant
     */
    public function test_cache_vendor_data()
    {
        $vendorId = $this->restaurant->user_id;
        $cacheKey = "vendor_data_{$vendorId}";

        Cache::forget($cacheKey);

        $result = $this->cacheService->cacheVendorData($vendorId);

        $this->assertTrue($result);
        $this->assertTrue(Cache::has($cacheKey));

        $cachedData = Cache::get($cacheKey);
        $this->assertIsArray($cachedData);
        $this->assertEquals('Test Restaurant', $cachedData['restaurant_name']);
    }

    /**
     * Test de mise en cache des paramètres système
     */
    public function test_cache_system_settings()
    {
        Settings::factory()->create([
            'key' => 'app_name',
            'value' => 'RestroSaaS Test'
        ]);

        $cacheKey = 'system_settings';
        Cache::forget($cacheKey);

        $result = $this->cacheService->cacheSystemSettings();

        $this->assertTrue($result);
        $this->assertTrue(Cache::has($cacheKey));

        $cachedSettings = Cache::get($cacheKey);
        $this->assertIsArray($cachedSettings);
        $this->assertArrayHasKey('app_name', $cachedSettings);
        $this->assertEquals('RestroSaaS Test', $cachedSettings['app_name']);
    }

    /**
     * Test d'invalidation du cache par restaurant
     */
    public function test_invalidate_vendor_cache()
    {
        $vendorId = $this->restaurant->user_id;

        // Mettre en cache plusieurs éléments
        $this->cacheService->cacheVendorCategories($vendorId);
        $this->cacheService->cacheVendorData($vendorId);

        // Vérifier que les caches existent
        $this->assertTrue(Cache::has("vendor_categories_{$vendorId}"));
        $this->assertTrue(Cache::has("vendor_data_{$vendorId}"));

        // Invalider le cache
        $result = $this->cacheService->invalidateVendorCache($vendorId);

        $this->assertTrue($result);
        $this->assertFalse(Cache::has("vendor_categories_{$vendorId}"));
        $this->assertFalse(Cache::has("vendor_data_{$vendorId}"));
    }

    /**
     * Test d'invalidation du cache par catégorie
     */
    public function test_invalidate_category_cache()
    {
        $categoryId = $this->category->id;

        // Mettre en cache
        $this->cacheService->cacheCategoryItems($categoryId);
        $this->assertTrue(Cache::has("category_items_{$categoryId}"));

        // Invalider
        $result = $this->cacheService->invalidateCategoryCache($categoryId);

        $this->assertTrue($result);
        $this->assertFalse(Cache::has("category_items_{$categoryId}"));
    }

    /**
     * Test de récupération depuis le cache avec fallback
     */
    public function test_get_cached_data_with_fallback()
    {
        $vendorId = $this->restaurant->user_id;
        $cacheKey = "vendor_categories_{$vendorId}";

        // Cache vide - doit utiliser le fallback
        Cache::forget($cacheKey);

        $categories = $this->cacheService->getCachedVendorCategories($vendorId);

        $this->assertIsArray($categories);
        $this->assertCount(1, $categories);

        // Vérifier que le cache a été créé par le fallback
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test de performances du cache
     */
    public function test_cache_performance_improvement()
    {
        $vendorId = $this->restaurant->user_id;

        // Premier appel (sans cache) - mesurer le temps
        Cache::flush();
        $startTime = microtime(true);
        $categories1 = $this->cacheService->getCachedVendorCategories($vendorId);
        $firstCallTime = microtime(true) - $startTime;

        // Deuxième appel (avec cache) - mesurer le temps
        $startTime = microtime(true);
        $categories2 = $this->cacheService->getCachedVendorCategories($vendorId);
        $secondCallTime = microtime(true) - $startTime;

        // Le deuxième appel doit être plus rapide
        $this->assertLessThan($firstCallTime, $secondCallTime);

        // Les données doivent être identiques
        $this->assertEquals($categories1, $categories2);
    }

    /**
     * Test de TTL (Time To Live) du cache
     */
    public function test_cache_ttl_expiration()
    {
        $vendorId = $this->restaurant->user_id;
        $cacheKey = "vendor_categories_{$vendorId}";

        // Mettre en cache avec un TTL très court (1 seconde)
        Cache::put($cacheKey, ['test' => 'data'], 1);
        $this->assertTrue(Cache::has($cacheKey));

        // Attendre l'expiration
        sleep(2);

        // Le cache doit avoir expiré
        $this->assertFalse(Cache::has($cacheKey));
    }

    /**
     * Test de statistiques du cache
     */
    public function test_cache_statistics()
    {
        $vendorId = $this->restaurant->user_id;

        // Faire plusieurs opérations de cache
        $this->cacheService->cacheVendorCategories($vendorId);
        $this->cacheService->cacheVendorData($vendorId);
        $this->cacheService->getCachedVendorCategories($vendorId);

        $stats = $this->cacheService->getCacheStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_keys', $stats);
        $this->assertArrayHasKey('cache_size', $stats);
        $this->assertGreaterThan(0, $stats['total_keys']);
    }

    /**
     * Test de nettoyage du cache
     */
    public function test_cache_cleanup()
    {
        $vendorId = $this->restaurant->user_id;

        // Créer plusieurs entrées de cache
        $this->cacheService->cacheVendorCategories($vendorId);
        $this->cacheService->cacheVendorData($vendorId);
        $this->cacheService->cacheSystemSettings();

        // Vérifier qu'elles existent
        $this->assertTrue(Cache::has("vendor_categories_{$vendorId}"));
        $this->assertTrue(Cache::has("vendor_data_{$vendorId}"));
        $this->assertTrue(Cache::has('system_settings'));

        // Nettoyer le cache
        $result = $this->cacheService->clearAllCache();

        $this->assertTrue($result);
        $this->assertFalse(Cache::has("vendor_categories_{$vendorId}"));
        $this->assertFalse(Cache::has("vendor_data_{$vendorId}"));
        $this->assertFalse(Cache::has('system_settings'));
    }

    /**
     * Test de réchauffement du cache
     */
    public function test_cache_warmup()
    {
        $vendorId = $this->restaurant->user_id;

        // Vider le cache
        Cache::flush();

        // Réchauffer le cache
        $result = $this->cacheService->warmupVendorCache($vendorId);

        $this->assertTrue($result);

        // Vérifier que les caches sont créés
        $this->assertTrue(Cache::has("vendor_categories_{$vendorId}"));
        $this->assertTrue(Cache::has("vendor_data_{$vendorId}"));
    }

    /**
     * Test de gestion d'erreur lors de la mise en cache
     */
    public function test_cache_error_handling()
    {
        // Tester avec un ID invalide
        $result = $this->cacheService->cacheVendorCategories(999999);

        // Ne doit pas lever d'exception
        $this->assertFalse($result);
        $this->assertFalse(Cache::has('vendor_categories_999999'));
    }

    /**
     * Test de sérialisation des données de cache
     */
    public function test_cache_data_serialization()
    {
        $vendorId = $this->restaurant->user_id;

        // Mettre en cache
        $this->cacheService->cacheVendorCategories($vendorId);

        // Récupérer et vérifier la structure
        $cachedData = Cache::get("vendor_categories_{$vendorId}");

        $this->assertIsArray($cachedData);

        // Vérifier que les données sont correctement sérialisées
        $category = $cachedData[0];
        $this->assertArrayHasKey('id', $category);
        $this->assertArrayHasKey('name', $category);
        $this->assertArrayHasKey('vendor_id', $category);
        $this->assertIsInt($category['id']);
        $this->assertIsString($category['name']);
    }
}
