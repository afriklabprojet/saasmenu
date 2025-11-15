<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Item;
use App\Services\CacheOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class OrderProcessingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $restaurant;
    protected $customer;
    protected $admin;
    protected $item;
    protected $category;
    protected $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les données de test
        $this->admin = User::factory()->create([
            'type' => 'admin',
            'email' => 'admin@test.com'
        ]);

        $this->restaurant = Restaurant::factory()->create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_slug' => 'test-restaurant',
            'is_active' => 1
        ]);

        $this->customer = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer@test.com'
        ]);

        $this->category = Category::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'name' => 'Main Dishes',
            'is_available' => 1
        ]);

        $this->item = Item::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'cat_id' => $this->category->id,
            'name' => 'Test Item',
            'price' => 25.99,
            'is_available' => 1,
            'tax' => 10.00
        ]);

        $this->cacheService = app(CacheOptimizationService::class);
    }

    /**
     * Test de création d'une commande complète
     */
    public function test_order_creation_success()
    {
        $this->actingAs($this->customer);

        $orderData = [
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'total_amount' => 28.59, // 25.99 + 10% tax
            'status' => 'pending',
            'order_type' => 'delivery',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 1,
                    'price' => 25.99,
                    'grand_total' => 25.99
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'vendor_id',
                        'customer_id',
                        'total_amount',
                        'status',
                        'order_type',
                        'created_at'
                    ]
                ]);

        // Vérifier en base de données
        $this->assertDatabaseHas('orders', [
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'status' => 'pending'
        ]);

        $order = Order::latest()->first();
        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'item_id' => $this->item->id,
            'quantity' => 1
        ]);
    }

    /**
     * Test de validation des données de commande
     */
    public function test_order_validation_fails_with_invalid_data()
    {
        $this->actingAs($this->customer);

        $invalidData = [
            'vendor_id' => 999, // Restaurant inexistant
            'total_amount' => -10, // Montant négatif
            'items' => [] // Aucun produit
        ];

        $response = $this->postJson('/api/orders', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['vendor_id', 'total_amount', 'items']);
    }

    /**
     * Test de mise à jour du statut de commande
     */
    public function test_order_status_update()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'status' => 'pending'
        ]);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'confirmed'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);
    }

    /**
     * Test de sécurité - accès aux commandes d'autres restaurants
     */
    public function test_order_access_security()
    {
        $otherRestaurant = Restaurant::factory()->create();
        $vendorUser = User::factory()->create([
            'type' => 'vendor',
            'restaurant_id' => $otherRestaurant->id
        ]);

        $order = Order::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id
        ]);

        $this->actingAs(User::find($vendorUser->id));

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test de performance avec cache
     */
    public function test_order_list_performance_with_cache()
    {
        // Créer plusieurs commandes
        Order::factory()->count(50)->create([
            'vendor_id' => $this->restaurant->user_id
        ]);

        $this->actingAs($this->admin);

        // Premier appel (sans cache)
        $startTime = microtime(true);
        $response1 = $this->getJson("/api/restaurants/{$this->restaurant->id}/orders");
        $firstCallTime = microtime(true) - $startTime;

        $response1->assertStatus(Response::HTTP_OK);

        // Deuxième appel (avec cache)
        $startTime = microtime(true);
        $response2 = $this->getJson("/api/restaurants/{$this->restaurant->id}/orders");
        $secondCallTime = microtime(true) - $startTime;

        $response2->assertStatus(Response::HTTP_OK);

        // Le deuxième appel doit être plus rapide
        $this->assertLessThan($firstCallTime, $secondCallTime);

        // Vérifier que les données sont identiques
        $this->assertEquals(
            $response1->json('data'),
            $response2->json('data')
        );
    }

    /**
     * Test d'invalidation du cache lors de mise à jour
     */
    public function test_cache_invalidation_on_order_update()
    {
        $this->actingAs($this->admin);

        $order = Order::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'status' => 'pending'
        ]);

        // Mettre en cache
        $this->cacheService->cacheVendorOrders($this->restaurant->user_id);

        // Vérifier que le cache existe
        $cacheKey = "vendor_orders_{$this->restaurant->user_id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Mettre à jour la commande
        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'confirmed'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        // Vérifier que le cache est invalidé
        $this->assertFalse(Cache::has($cacheKey));
    }

    /**
     * Test de calcul des taxes
     */
    public function test_order_tax_calculation()
    {
        $this->actingAs($this->customer);

        $orderData = [
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 2,
                    'price' => 25.99
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(Response::HTTP_CREATED);

        $order = Order::latest()->first();

        // Vérifier le calcul des taxes (10% sur 51.98)
        $expectedSubtotal = 51.98;
        $expectedTax = 5.20; // 10% de 51.98
        $expectedTotal = 57.18;

        $this->assertEquals($expectedSubtotal, $order->subtotal);
        $this->assertEquals($expectedTax, $order->tax_amount);
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    /**
     * Test de gestion des stocks
     */
    public function test_stock_management_on_order()
    {
        // Définir un stock limité
        $this->item->update(['stock_quantity' => 5]);

        $this->actingAs($this->customer);

        $orderData = [
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 3,
                    'price' => 25.99
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(Response::HTTP_CREATED);

        // Vérifier que le stock a été décrémenté
        $this->item->refresh();
        $this->assertEquals(2, $this->item->stock_quantity);
    }

    /**
     * Test de commande avec stock insuffisant
     */
    public function test_order_fails_with_insufficient_stock()
    {
        $this->item->update(['stock_quantity' => 1]);

        $this->actingAs($this->customer);

        $orderData = [
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 5, // Plus que le stock disponible
                    'price' => 25.99
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }
}
