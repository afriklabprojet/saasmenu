<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PricingPlan;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SubscriptionLimitsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $vendorFreePlan;
    protected $vendorStarterPlan;
    protected $vendorEnterprisePlan;
    protected $freePlan;
    protected $starterPlan;
    protected $enterprisePlan;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les plans
        $this->freePlan = PricingPlan::create([
            'name' => 'Plan Gratuit',
            'price' => 0,
            'duration' => 1,
            'plan_type' => 1,
            'products_limit' => 5,
            'categories_limit' => 1,
            'staff_limit' => 1,
            'order_limit' => -1,
            'appointment_limit' => -1,
            'whatsapp_integration' => 1,
            'analytics' => 2,
            'custom_domain' => 2,
            'is_available' => 1,
        ]);

        $this->starterPlan = PricingPlan::create([
            'name' => 'Starter',
            'price' => 29,
            'duration' => 1,
            'plan_type' => 1,
            'products_limit' => 50,
            'categories_limit' => 15,
            'staff_limit' => 3,
            'order_limit' => -1,
            'appointment_limit' => -1,
            'whatsapp_integration' => 1,
            'analytics' => 1,
            'custom_domain' => 1,
            'is_available' => 1,
        ]);

        $this->enterprisePlan = PricingPlan::create([
            'name' => 'Enterprise',
            'price' => 199,
            'duration' => 1,
            'plan_type' => 1,
            'products_limit' => -1,
            'categories_limit' => -1,
            'staff_limit' => -1,
            'order_limit' => -1,
            'appointment_limit' => -1,
            'whatsapp_integration' => 1,
            'analytics' => 1,
            'custom_domain' => 1,
            'is_available' => 1,
        ]);

        // Créer les utilisateurs
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'type' => 1,
            'is_available' => 1,
        ]);

        $this->vendorFreePlan = User::create([
            'name' => 'Vendor Free',
            'email' => 'vendor1@test.com',
            'password' => bcrypt('vendor123'),
            'type' => 2,
            'plan_id' => $this->freePlan->id,
            'is_available' => 1,
        ]);

        $this->vendorStarterPlan = User::create([
            'name' => 'Vendor Starter',
            'email' => 'vendor2@test.com',
            'password' => bcrypt('vendor123'),
            'type' => 2,
            'plan_id' => $this->starterPlan->id,
            'is_available' => 1,
        ]);

        $this->vendorEnterprisePlan = User::create([
            'name' => 'Vendor Enterprise',
            'email' => 'vendor3@test.com',
            'password' => bcrypt('vendor123'),
            'type' => 2,
            'plan_id' => $this->enterprisePlan->id,
            'is_available' => 1,
        ]);
    }

    /** @test */
    public function test_helper_get_plan_info_returns_correct_structure()
    {
        $planInfo = \App\Helpers\helper::getPlanInfo($this->vendorFreePlan->id);

        $this->assertIsArray($planInfo);
        $this->assertArrayHasKey('plan_name', $planInfo);
        $this->assertArrayHasKey('products_limit', $planInfo);
        $this->assertArrayHasKey('categories_limit', $planInfo);
        $this->assertArrayHasKey('staff_limit', $planInfo);
        $this->assertArrayHasKey('whatsapp_integration', $planInfo);
        $this->assertArrayHasKey('analytics', $planInfo);

        $this->assertEquals('Plan Gratuit', $planInfo['plan_name']);
        $this->assertEquals(5, $planInfo['products_limit']);
        $this->assertEquals(1, $planInfo['categories_limit']);
        $this->assertEquals(1, $planInfo['staff_limit']);
    }

    /** @test */
    public function test_helper_get_plan_info_handles_null_vendor()
    {
        $planInfo = \App\Helpers\helper::getPlanInfo(99999);

        $this->assertIsArray($planInfo);
        $this->assertEquals('No Plan', $planInfo['plan_name']);
        $this->assertEquals(0, $planInfo['products_limit']);
    }

    /** @test */
    public function test_vendor_can_add_product_within_limit()
    {
        $this->actingAs($this->vendorFreePlan);

        // Créer une catégorie d'abord
        $category = Category::create([
            'vendor_id' => $this->vendorFreePlan->id,
            'name' => 'Test Category',
            'slug' => 'test-category-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Ajouter 3 produits (sous la limite de 5)
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post('/admin/products/save', [
                'product_name' => 'Product ' . $i,
                'category' => $category->id,
                'price' => 10 + $i,
                'original_price' => 15 + $i,
                'qty' => 10,
                'has_variants' => 0,
            ]);

            $response->assertStatus(302); // Redirection après succès
        }

        // Vérifier qu'on a bien 3 produits
        $this->assertEquals(3, Item::where('vendor_id', $this->vendorFreePlan->id)->count());
    }

    /** @test */
    public function test_vendor_cannot_exceed_product_limit()
    {
        $this->actingAs($this->vendorFreePlan);

        // Créer une catégorie
        $category = Category::create([
            'vendor_id' => $this->vendorFreePlan->id,
            'name' => 'Test Category',
            'slug' => 'test-category-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer 5 produits (atteindre la limite)
        for ($i = 1; $i <= 5; $i++) {
            Item::create([
                'vendor_id' => $this->vendorFreePlan->id,
                'cat_id' => $category->id,
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i . '-' . time(),
                'price' => 10,
                'item_original_price' => 15,
                'tax' => null,
                'is_available' => 1,
            ]);
        }

        // Essayer d'accéder au formulaire d'ajout
        $response = $this->get('/admin/products/add');

        // Doit être redirigé avec un message d'erreur
        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_vendor_with_unlimited_plan_can_add_many_products()
    {
        $this->actingAs($this->vendorEnterprisePlan);

        // Créer une catégorie
        $category = Category::create([
            'vendor_id' => $this->vendorEnterprisePlan->id,
            'name' => 'Test Category',
            'slug' => 'test-category-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer 100 produits (bien au-delà de la limite normale)
        for ($i = 1; $i <= 100; $i++) {
            Item::create([
                'vendor_id' => $this->vendorEnterprisePlan->id,
                'cat_id' => $category->id,
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i . '-' . time() . '-' . $i,
                'price' => 10,
                'item_original_price' => 15,
                'tax' => null,
                'is_available' => 1,
            ]);
        }

        // Devrait toujours pouvoir accéder au formulaire d'ajout
        $response = $this->get('/admin/products/add');
        $response->assertStatus(200);

        // Vérifier qu'on a bien 100 produits
        $this->assertEquals(100, Item::where('vendor_id', $this->vendorEnterprisePlan->id)->count());
    }

    /** @test */
    public function test_vendor_cannot_exceed_category_limit()
    {
        $this->actingAs($this->vendorFreePlan);

        // Créer 1 catégorie (atteindre la limite)
        Category::create([
            'vendor_id' => $this->vendorFreePlan->id,
            'name' => 'Category 1',
            'slug' => 'category-1-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Essayer d'ajouter une 2ème catégorie
        $response = $this->post('/admin/categories/save_category', [
            'category_name' => 'Category 2',
        ]);

        // Doit être redirigé avec erreur
        $response->assertRedirect('/admin/categories/add');
        $response->assertSessionHas('error');

        // Vérifier qu'on a toujours 1 seule catégorie
        $this->assertEquals(1, Category::where('vendor_id', $this->vendorFreePlan->id)
                                      ->where('is_deleted', 2)
                                      ->count());
    }

    /** @test */
    public function test_admin_is_not_limited()
    {
        $this->actingAs($this->admin);

        // Admin peut accéder aux pages sans vérification de limite
        $response = $this->get('/admin/products');
        $response->assertStatus(200);

        // Pas d'indicateur de limite affiché pour l'admin
        $response->assertDontSee('Produits:');
    }

    /** @test */
    public function test_product_list_shows_limit_indicator()
    {
        $this->actingAs($this->vendorFreePlan);

        // Créer 3 produits
        $category = Category::create([
            'vendor_id' => $this->vendorFreePlan->id,
            'name' => 'Test Category',
            'slug' => 'test-category-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            Item::create([
                'vendor_id' => $this->vendorFreePlan->id,
                'cat_id' => $category->id,
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i . '-' . time(),
                'price' => 10,
                'item_original_price' => 15,
                'tax' => null,
                'is_available' => 1,
            ]);
        }

        $response = $this->get('/admin/products');

        // Vérifier que l'indicateur est affiché
        $response->assertSee('Produits:');
        $response->assertSee('3/5');
        $response->assertSee('60%');
    }

    /** @test */
    public function test_plan_creation_saves_all_limits()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/plan/save_plan', [
            'plan_name' => 'Test Plan',
            'plan_price' => 49,
            'plan_duration' => 1,
            'type' => 1,
            'plan_description' => 'Test plan description',
            'plan_features' => ['Feature 1', 'Feature 2'],
            'products_limit_type' => 1,
            'products_limit' => 20,
            'categories_limit_type' => 1,
            'categories_limit' => 10,
            'staff_limit_type' => 1,
            'staff_limit' => 5,
            'whatsapp_integration' => 'on',
            'analytics' => 'on',
            'themecheckbox' => ['1'],
        ]);

        $response->assertRedirect('/admin/plan');

        // Vérifier en base de données
        $plan = PricingPlan::where('name', 'Test Plan')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(20, $plan->products_limit);
        $this->assertEquals(10, $plan->categories_limit);
        $this->assertEquals(5, $plan->staff_limit);
        $this->assertEquals(1, $plan->whatsapp_integration);
        $this->assertEquals(1, $plan->analytics);
    }

    /** @test */
    public function test_plan_with_unlimited_limits()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/plan/save_plan', [
            'plan_name' => 'Unlimited Plan',
            'plan_price' => 299,
            'plan_duration' => 1,
            'type' => 1,
            'plan_description' => 'Unlimited everything',
            'plan_features' => ['Unlimited Features'],
            'products_limit_type' => 2, // Unlimited
            'categories_limit_type' => 2, // Unlimited
            'staff_limit_type' => 2, // Unlimited
            'themecheckbox' => ['1'],
        ]);

        $plan = PricingPlan::where('name', 'Unlimited Plan')->first();
        $this->assertEquals(-1, $plan->products_limit);
        $this->assertEquals(-1, $plan->categories_limit);
        $this->assertEquals(-1, $plan->staff_limit);
    }

    /** @test */
    public function test_warning_message_at_80_percent()
    {
        $this->actingAs($this->vendorFreePlan);

        // Créer une catégorie
        $category = Category::create([
            'vendor_id' => $this->vendorFreePlan->id,
            'name' => 'Test Category',
            'slug' => 'test-category-' . time(),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer 4 produits (80% de 5)
        for ($i = 1; $i <= 4; $i++) {
            Item::create([
                'vendor_id' => $this->vendorFreePlan->id,
                'cat_id' => $category->id,
                'name' => 'Product ' . $i,
                'slug' => 'product-' . $i . '-' . time(),
                'price' => 10,
                'item_original_price' => 15,
                'tax' => null,
                'is_available' => 1,
            ]);
        }

        // Accéder à la page d'ajout
        $response = $this->get('/admin/products/add');

        // Vérifier le message d'avertissement
        $response->assertSee('Attention');
        $response->assertSee('4/5');
        $response->assertSee('80%');
        $response->assertSee('Upgrader maintenant');
    }
}
