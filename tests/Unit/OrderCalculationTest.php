<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Settings;
use App\Models\Item;
use App\Models\Category;
use App\Models\Cart;
use App\Models\DeliveryArea;
use App\Models\Variants;
use App\Http\Controllers\web\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use ReflectionMethod;

/**
 * Tests unitaires pour les méthodes de calcul OrderController
 * - calculateTax()
 * - calculateDeliveryCharge()
 * - validateCartStock()
 */
class OrderCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un vendor
        $this->vendor = User::factory()->create([
            'type' => 2,
            'is_available' => 1,
            'is_verified' => 1,
        ]);

        Settings::create([
            'vendor_id' => $this->vendor->id,
            'timezone' => 'UTC',
            'currency' => 'USD',
            'min_order' => 10.00,
            'default_tax' => 10,
            'delivery_charge' => 5.00,
        ]);

        $this->controller = new OrderController();
    }

    /** @test */
    public function test_calculate_tax_with_percentage_tax()
    {
        // Créer category et item avec taxe en pourcentage
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Test Item',
            'price' => 100.00,
            'tax' => json_encode([
                ['name' => 'VAT', 'type' => '2', 'tax' => '10'], // 10% sur 100 = 10
                ['name' => 'Service', 'type' => '2', 'tax' => '5'], // 5% sur 100 = 5
            ]),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer panier
        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => null,
            'session_id' => 'test-session',
            'item_id' => $item->id,
            'qty' => 1,
            'price' => 100.00,
            'tax' => $item->tax,
        ]);

        // Utiliser reflection pour appeler méthode privée
        $method = new ReflectionMethod(OrderController::class, 'calculateTax');
        $method->setAccessible(true);

        $cartData = Cart::where('vendor_id', $this->vendor->id)->get();
        $taxDetails = $method->invoke($this->controller, $cartData, $this->vendor->id);

        // Vérifier résultats
        $this->assertIsArray($taxDetails);
        $this->assertArrayHasKey('tax_total', $taxDetails);
        $this->assertArrayHasKey('taxes', $taxDetails);

        // Total tax devrait être 15 (10 + 5)
        $this->assertEquals(15.00, $taxDetails['tax_total']);

        // Vérifier détails des taxes
        $this->assertCount(2, $taxDetails['taxes']);
        $this->assertEquals('VAT', $taxDetails['taxes'][0]['name']);
        $this->assertEquals(10.00, $taxDetails['taxes'][0]['amount']);
    }

    /** @test */
    public function test_calculate_tax_with_fixed_tax()
    {
        // Créer item avec taxe fixe
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Test Item',
            'price' => 50.00,
            'tax' => json_encode([
                ['name' => 'Fixed Fee', 'type' => '1', 'tax' => '5'], // Montant fixe de 5
            ]),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => null,
            'session_id' => 'test-session',
            'item_id' => $item->id,
            'qty' => 2,
            'price' => 50.00,
            'tax' => $item->tax,
        ]);

        $method = new ReflectionMethod(OrderController::class, 'calculateTax');
        $method->setAccessible(true);

        $cartData = Cart::where('vendor_id', $this->vendor->id)->get();
        $taxDetails = $method->invoke($this->controller, $cartData, $this->vendor->id);

        // Pour type 1 (fixed), tax s'applique par item
        // 2 items × 5 = 10
        $this->assertEquals(10.00, $taxDetails['tax_total']);
    }

    /** @test */
    public function test_calculate_tax_aggregates_same_tax_names()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Deux items différents avec même nom de taxe
        $item1 = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Item 1',
            'price' => 100.00,
            'tax' => json_encode([
                ['name' => 'VAT', 'type' => '2', 'tax' => '10'],
            ]),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item2 = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Item 2',
            'price' => 50.00,
            'tax' => json_encode([
                ['name' => 'VAT', 'type' => '2', 'tax' => '10'],
            ]),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        Cart::create([
            'vendor_id' => $this->vendor->id,
            'item_id' => $item1->id,
            'qty' => 1,
            'price' => 100.00,
            'tax' => $item1->tax,
        ]);

        Cart::create([
            'vendor_id' => $this->vendor->id,
            'item_id' => $item2->id,
            'qty' => 1,
            'price' => 50.00,
            'tax' => $item2->tax,
        ]);

        $method = new ReflectionMethod(OrderController::class, 'calculateTax');
        $method->setAccessible(true);

        $cartData = Cart::where('vendor_id', $this->vendor->id)->get();
        $taxDetails = $method->invoke($this->controller, $cartData, $this->vendor->id);

        // Total VAT: 10% de 100 + 10% de 50 = 10 + 5 = 15
        $this->assertEquals(15.00, $taxDetails['tax_total']);

        // Doit avoir une seule entrée VAT agrégée
        $this->assertCount(1, $taxDetails['taxes']);
        $this->assertEquals('VAT', $taxDetails['taxes'][0]['name']);
        $this->assertEquals(15.00, $taxDetails['taxes'][0]['amount']);
    }

    /** @test */
    public function test_calculate_delivery_charge_with_specific_area()
    {
        // Créer zones de livraison
        $specificArea = DeliveryArea::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Zone Premium',
            'price' => 10.00,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $defaultArea = DeliveryArea::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Default Zone',
            'price' => 5.00,
            'is_available' => 1,
            'is_deleted' => 2,
            'is_default' => 1,
        ]);

        $method = new ReflectionMethod(OrderController::class, 'calculateDeliveryCharge');
        $method->setAccessible(true);

        $charge = $method->invoke($this->controller, $specificArea->id, $this->vendor->id);

        // Devrait retourner le prix de la zone spécifique
        $this->assertEquals(10.00, $charge);
    }

    /** @test */
    public function test_calculate_delivery_charge_falls_back_to_default()
    {
        // Créer uniquement zone par défaut
        $defaultArea = DeliveryArea::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Default Zone',
            'price' => 5.00,
            'is_available' => 1,
            'is_deleted' => 2,
            'is_default' => 1,
        ]);

        $method = new ReflectionMethod(OrderController::class, 'calculateDeliveryCharge');
        $method->setAccessible(true);

        // Passer un ID invalide
        $charge = $method->invoke($this->controller, 9999, $this->vendor->id);

        // Devrait retourner le prix de la zone par défaut
        $this->assertEquals(5.00, $charge);
    }

    /** @test */
    public function test_calculate_delivery_charge_returns_zero_if_no_area()
    {
        // Aucune zone de livraison

        $method = new ReflectionMethod(OrderController::class, 'calculateDeliveryCharge');
        $method->setAccessible(true);

        $charge = $method->invoke($this->controller, null, $this->vendor->id);

        // Devrait retourner 0
        $this->assertEquals(0, $charge);
    }

    /** @test */
    public function test_validate_cart_stock_success()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Test Item',
            'price' => 25.00,
            'min_order' => 1,
            'max_order' => 10,
            'stock_qty' => 100,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $cartData = collect([
            (object)[
                'item_id' => $item->id,
                'variant_id' => null,
                'qty' => 5,
                'name' => 'Test Item',
            ]
        ]);

        $method = new ReflectionMethod(OrderController::class, 'validateCartStock');
        $method->setAccessible(true);

        // Ne devrait pas lancer d'exception
        $this->expectNotToPerformAssertions();
        $method->invoke($this->controller, $cartData, $this->vendor->id);
    }

    /** @test */
    public function test_validate_cart_stock_fails_insufficient_stock()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Low Stock Item',
            'price' => 25.00,
            'stock_qty' => 2, // Stock faible
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $cartData = collect([
            (object)[
                'item_id' => $item->id,
                'variant_id' => null,
                'qty' => 5, // Plus que le stock
                'name' => 'Low Stock Item',
            ]
        ]);

        $method = new ReflectionMethod(OrderController::class, 'validateCartStock');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('stock insuffisant');
        $method->invoke($this->controller, $cartData, $this->vendor->id);
    }

    /** @test */
    public function test_validate_cart_stock_fails_min_order()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Min Order Item',
            'price' => 25.00,
            'min_order' => 5, // Minimum 5
            'stock_qty' => 100,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $cartData = collect([
            (object)[
                'item_id' => $item->id,
                'variant_id' => null,
                'qty' => 2, // Moins que le minimum
                'name' => 'Min Order Item',
            ]
        ]);

        $method = new ReflectionMethod(OrderController::class, 'validateCartStock');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('commande minimum');
        $method->invoke($this->controller, $cartData, $this->vendor->id);
    }

    /** @test */
    public function test_validate_cart_stock_with_variants()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Variant Item',
            'price' => 25.00,
            'stock_qty' => 100,
            'has_variants' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $variant = Variants::create([
            'item_id' => $item->id,
            'name' => 'Size L',
            'price' => 30.00,
            'stock_qty' => 10,
            'min_order' => 1,
            'max_order' => 5,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $cartData = collect([
            (object)[
                'item_id' => $item->id,
                'variant_id' => $variant->id,
                'qty' => 3,
                'name' => 'Variant Item',
            ]
        ]);

        $method = new ReflectionMethod(OrderController::class, 'validateCartStock');
        $method->setAccessible(true);

        // Ne devrait pas lancer d'exception
        $this->expectNotToPerformAssertions();
        $method->invoke($this->controller, $cartData, $this->vendor->id);
    }

    /** @test */
    public function test_validate_cart_stock_variant_max_order_exceeded()
    {
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Variant Item',
            'price' => 25.00,
            'has_variants' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $variant = Variants::create([
            'item_id' => $item->id,
            'name' => 'Size L',
            'price' => 30.00,
            'stock_qty' => 100,
            'min_order' => 1,
            'max_order' => 5, // Maximum 5
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $cartData = collect([
            (object)[
                'item_id' => $item->id,
                'variant_id' => $variant->id,
                'qty' => 10, // Plus que le maximum
                'name' => 'Variant Item',
            ]
        ]);

        $method = new ReflectionMethod(OrderController::class, 'validateCartStock');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('commande maximum');
        $method->invoke($this->controller, $cartData, $this->vendor->id);
    }
}
