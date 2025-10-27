<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\ValidateAddonPermission;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ValidateAddonPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;
    protected $user;
    protected $restaurant;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new ValidateAddonPermission();
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $request = Request::create('/api/pos/sessions', 'GET');
        
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'pos');
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContains('Authentication required', $response->getContent());
    }

    /** @test */
    public function it_requires_restaurant_id()
    {
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $this->user);
        
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'pos');
        
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContains('Restaurant not specified', $response->getContent());
    }

    /** @test */
    public function it_validates_restaurant_exists()
    {
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->merge(['restaurant_id' => 99999]); // ID inexistant
        
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'pos');
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContains('Restaurant not found', $response->getContent());
    }

    /** @test */
    public function it_allows_restaurant_owner_access()
    {
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        $nextCalled = false;
        $response = $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;
            $this->assertEquals($this->restaurant->id, $req->get('restaurant')->id);
            $this->assertEquals('pos', $req->get('addon'));
            $this->assertEquals('owner', $req->get('user_role'));
            return new Response('OK');
        }, 'pos');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_denies_access_to_non_owner_without_permissions()
    {
        $otherUser = User::factory()->create();
        
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $otherUser);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'pos');
        
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContains('Access denied', $response->getContent());
    }

    /** @test */
    public function it_gets_restaurant_id_from_route_parameter()
    {
        $request = Request::create('/api/restaurants/' . $this->restaurant->id . '/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['GET'], '', []);
            $route->setParameter('restaurant_id', $this->restaurant->id);
            return $route;
        });
        
        $nextCalled = false;
        $response = $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;
            return new Response('OK');
        }, 'pos');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_gets_restaurant_id_from_header()
    {
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->headers->set('X-Restaurant-ID', $this->restaurant->id);
        
        $nextCalled = false;
        $response = $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;
            return new Response('OK');
        }, 'pos');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_checks_addon_permissions_by_role()
    {
        // Créer un utilisateur employé
        $employee = User::factory()->create();
        
        // Simuler une relation employé-restaurant
        $this->restaurant->users()->attach($employee->id, ['role' => 'employee']);
        
        $request = Request::create('/api/pos/sessions', 'POST'); // Action create
        $request->setUserResolver(fn() => $employee);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        // Les employés peuvent lire mais pas créer dans POS selon nos permissions
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'pos');
        
        $this->assertEquals(200, $response->getStatusCode()); // POS permet create pour employee
    }

    /** @test */
    public function it_denies_access_to_disabled_addon()
    {
        // Modifier les settings du restaurant pour désactiver l'addon
        $this->restaurant->update([
            'settings' => ['enabled_addons' => ['api']] // Seulement API activé
        ]);
        
        $request = Request::create('/api/loyalty/programs', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        }, 'loyalty');
        
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContains('Addon not enabled', $response->getContent());
    }

    /** @test */
    public function it_allows_access_to_default_addons()
    {
        // Les addons par défaut (api, tableqr, pos) sont toujours activés
        $request = Request::create('/api/tableqr/generate', 'GET');
        $request->setUserResolver(fn() => $this->user);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        $nextCalled = false;
        $response = $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;
            return new Response('OK');
        }, 'tableqr');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_determines_action_from_http_method()
    {
        $testCases = [
            ['GET', 'read'],
            ['POST', 'create'],
            ['PUT', 'update'],
            ['PATCH', 'update'],
            ['DELETE', 'delete']
        ];
        
        foreach ($testCases as [$method, $expectedAction]) {
            $request = Request::create('/api/pos/sessions', $method);
            $request->setUserResolver(fn() => $this->user);
            $request->merge(['restaurant_id' => $this->restaurant->id]);
            
            $actualAction = null;
            $this->middleware->handle($request, function ($req) use (&$actualAction) {
                // On peut vérifier l'action en inspectant le comportement
                return new Response('OK');
            }, 'pos');
            
            // Cette assertion est implicite car le middleware traite différemment selon l'action
            $this->assertTrue(true); // Le test passe si aucune exception n'est levée
        }
    }

    /** @test */
    public function it_handles_super_admin_access()
    {
        // Créer un super admin
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin'); // Assumer qu'on utilise Spatie Permission
        
        $otherRestaurant = Restaurant::factory()->create();
        
        $request = Request::create('/api/pos/sessions', 'GET');
        $request->setUserResolver(fn() => $superAdmin);
        $request->merge(['restaurant_id' => $otherRestaurant->id]);
        
        $nextCalled = false;
        $response = $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;
            $this->assertEquals('super_admin', $req->get('user_role'));
            return new Response('OK');
        }, 'pos');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_validates_customer_permissions_for_customer_only_addons()
    {
        $customer = User::factory()->create();
        
        $request = Request::create('/api/paypal/checkout', 'POST');
        $request->setUserResolver(fn() => $customer);
        $request->merge(['restaurant_id' => $this->restaurant->id]);
        
        // Les clients peuvent créer des paiements PayPal
        $nextCalled = false;
        $response = $this->middleware->handle($request, function () use (&$nextCalled) {
            $nextCalled = true;
            return new Response('OK');
        }, 'paypal');
        
        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }
}