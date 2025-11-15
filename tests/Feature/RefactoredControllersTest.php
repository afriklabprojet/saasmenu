<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Item;

class RefactoredControllersTest extends TestCase
{
    use RefreshDatabase;

    private $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test vendor
        $this->vendor = User::factory()->create([
            'type' => 2,
            'slug' => 'test-vendor',
            'name' => 'Test Restaurant',
            'email' => 'vendor@test.com'
        ]);
    }

    /** @test */
    public function admin_login_page_loads_successfully()
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
        $this->assertStringContainsString('login', $response->getContent());
    }

    /** @test */
    public function refactored_home_controller_works()
    {
        $response = $this->get('/test-vendor');

        // Should load successfully or redirect
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function cart_controller_routes_exist()
    {
        // Test cart page
        $response = $this->get('/test-vendor/cart');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Test add to cart API (should require POST)
        $response = $this->get('/add-to-cart');
        $this->assertContains($response->getStatusCode(), [405, 302]);
    }

    /** @test */
    public function page_controller_routes_work()
    {
        $response = $this->get('/test-vendor/privacy-policy');
        $this->assertContains($response->getStatusCode(), [200, 302]);

        $response = $this->get('/test-vendor/terms');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function order_controller_routes_exist()
    {
        $response = $this->get('/test-vendor/checkout');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function contact_controller_routes_work()
    {
        $response = $this->get('/test-vendor/book');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function product_controller_routes_function()
    {
        // Create a test product
        $item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Product'
        ]);

        $response = $this->get("/test-vendor/product/{$item->id}");
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function all_admin_routes_have_correct_controllers()
    {
        $routes = Route::getRoutes();
        $adminRoutes = collect($routes)->filter(function ($route) {
            return str_starts_with($route->uri(), 'admin/');
        });

        foreach ($adminRoutes as $route) {
            $action = $route->getAction();
            if (isset($action['controller'])) {
                // Ensure no old namespace issues
                $this->assertStringNotContainsString('admin\\App\\Http\\Controllers', $action['controller']);
            }
        }
    }

    /** @test */
    public function all_front_routes_use_new_controllers()
    {
        $routes = Route::getRoutes();
        $frontRoutes = collect($routes)->filter(function ($route) {
            $name = $route->getName();
            return $name && str_starts_with($name, 'front.');
        });

        $newControllers = [
            'CartController',
            'OrderController',
            'ProductController',
            'PageController',
            'ContactController',
            'RefactoredHomeController'
        ];

        $usedControllers = [];
        foreach ($frontRoutes as $route) {
            $action = $route->getAction();
            if (isset($action['controller'])) {
                $controller = class_basename($action['controller']);
                $usedControllers[] = $controller;
            }
        }

        // Verify our new controllers are being used
        foreach ($newControllers as $controller) {
            $this->assertContains($controller, $usedControllers,
                "Controller {$controller} should be used in front routes");
        }
    }
}
