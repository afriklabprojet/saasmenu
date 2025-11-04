<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $customer;
    protected $admin;
    protected $vendorUser;
    protected $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les utilisateurs de test
        $this->admin = User::factory()->create([
            'type' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);

        $this->restaurant = Restaurant::factory()->create([
            'restaurant_name' => 'Test Restaurant',
            'is_active' => 1
        ]);

        $this->vendorUser = User::factory()->create([
            'type' => 'vendor',
            'email' => 'vendor@test.com',
            'password' => Hash::make('password123'),
            'restaurant_id' => $this->restaurant->id
        ]);

        $this->customer = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test de connexion réussie
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'type'
                        ],
                        'token'
                    ]
                ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test d'échec de connexion avec mot de passe incorrect
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    /**
     * Test de validation des champs de connexion
     */
    public function test_login_validation_errors()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => ''
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test d'inscription d'un nouveau client
     */
    public function test_customer_registration_success()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'customer'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'type'
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'type' => 'customer'
        ]);
    }

    /**
     * Test de protection contre les attaques par force brute
     */
    public function test_rate_limiting_on_login_attempts()
    {
        $credentials = [
            'email' => 'customer@test.com',
            'password' => 'wrongpassword'
        ];

        // Faire plusieurs tentatives de connexion échouées
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', $credentials);
        }

        // La dernière tentative devrait être bloquée
        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Test d'accès aux ressources protégées sans token
     */
    public function test_protected_route_requires_authentication()
    {
        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test d'accès aux ressources protégées avec token valide
     */
    public function test_protected_route_with_valid_token()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'data' => [
                        'id' => $this->customer->id,
                        'email' => $this->customer->email
                    ]
                ]);
    }

    /**
     * Test de contrôle d'accès basé sur les rôles - Admin
     */
    public function test_admin_can_access_admin_routes()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test de contrôle d'accès basé sur les rôles - Customer
     */
    public function test_customer_cannot_access_admin_routes()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test de contrôle d'accès basé sur les rôles - Vendor
     */
    public function test_vendor_can_access_own_restaurant_data()
    {
        Sanctum::actingAs($this->vendorUser);

        $response = $this->getJson("/api/vendor/restaurant/{$this->restaurant->id}");

        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test de sécurité - Vendor ne peut pas accéder aux données d'autres restaurants
     */
    public function test_vendor_cannot_access_other_restaurant_data()
    {
        $otherRestaurant = Restaurant::factory()->create();

        Sanctum::actingAs($this->vendorUser);

        $response = $this->getJson("/api/vendor/restaurant/{$otherRestaurant->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test de déconnexion
     */
    public function test_user_can_logout()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'message' => 'Successfully logged out'
                ]);
    }

    /**
     * Test de sécurité - Protection contre l'injection SQL
     */
    public function test_sql_injection_protection_in_login()
    {
        $maliciousPayload = [
            'email' => "admin@test.com'; DROP TABLE users; --",
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $maliciousPayload);

        // La requête doit échouer de manière sécurisée
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Vérifier que la table users existe toujours
        $this->assertDatabaseHas('users', [
            'email' => 'admin@test.com'
        ]);
    }

    /**
     * Test de sécurité - Protection contre le XSS
     */
    public function test_xss_protection_in_registration()
    {
        $maliciousData = [
            'name' => '<script>alert("XSS")</script>John Doe',
            'email' => 'xss@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'customer'
        ];

        $response = $this->postJson('/api/register', $maliciousData);

        $response->assertStatus(Response::HTTP_CREATED);

        // Vérifier que le script est échappé ou supprimé
        $user = User::where('email', 'xss@example.com')->first();
        $this->assertStringNotContainsString('<script>', $user->name);
        $this->assertStringNotContainsString('alert', $user->name);
    }

    /**
     * Test de sécurité - Validation des tokens expirés
     */
    public function test_expired_token_is_rejected()
    {
        // Créer un token pour l'utilisateur
        $token = $this->customer->createToken('test-token');

        // Simuler l'expiration du token en le supprimant
        $token->accessToken->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/user/profile');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test de changement de mot de passe
     */
    public function test_password_change_with_valid_current_password()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/user/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        // Vérifier que le nouveau mot de passe fonctionne
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'customer@test.com',
            'password' => 'newpassword123'
        ]);

        $loginResponse->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test d'échec de changement de mot de passe avec ancien mot de passe incorrect
     */
    public function test_password_change_fails_with_wrong_current_password()
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/user/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['current_password']);
    }

    /**
     * Test de validation de la complexité du mot de passe
     */
    public function test_password_complexity_validation()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Mot de passe trop simple
            'password_confirmation' => '123',
            'type' => 'customer'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['password']);
    }
}
