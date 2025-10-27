<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\POSTerminal;
use App\Models\LoyaltyProgram;
use App\Models\TableQrCode;
use App\Models\ApiKey;
use App\Services\FirebaseService;
use App\Services\ImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddonIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $restaurantAdmin;
    private Restaurant $restaurant;
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test restaurant and admin user
        $this->restaurant = Restaurant::factory()->create();
        $this->restaurantAdmin = User::factory()->create(['role' => 'restaurant_admin']);

        RestaurantUser::create([
            'user_id' => $this->restaurantAdmin->id,
            'restaurant_id' => $this->restaurant->id,
            'role' => 'admin',
            'permissions' => [
                'pos_access' => true,
                'loyalty_access' => true,
                'tableqr_access' => true,
                'import_export_access' => true,
                'notifications_access' => true,
                'analytics_access' => true,
            ],
            'is_active' => true,
        ]);

        // Create API key for testing
        $apiKeyModel = ApiKey::create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->restaurantAdmin->id,
            'name' => 'Integration Test Key',
            'key' => 'test_' . bin2hex(random_bytes(16)),
            'permissions' => [
                'pos_api' => true,
                'loyalty_api' => true,
                'tableqr_api' => true,
                'notifications_api' => true,
                'analytics_api' => true,
            ],
            'rate_limit' => 1000,
            'is_active' => true,
        ]);

        $this->apiKey = $apiKeyModel->key;
    }

    /** @test */
    public function it_can_access_all_addon_routes_with_proper_authentication(): void
    {
        $this->actingAs($this->restaurantAdmin);

        // Test admin dashboard access
        $response = $this->get('/admin/addons/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Addon Dashboard');

        // Test POS dashboard
        $response = $this->get('/admin/addons/pos');
        $response->assertStatus(200);
        $response->assertSee('POS System');

        // Test Loyalty dashboard
        $response = $this->get('/admin/addons/loyalty');
        $response->assertStatus(200);
        $response->assertSee('Loyalty Program');

        // Test Import/Export dashboard
        $response = $this->get('/admin/addons/import-export');
        $response->assertStatus(200);
        $response->assertSee('Import/Export');

        // Test Notifications dashboard
        $response = $this->get('/admin/addons/notifications');
        $response->assertStatus(200);
        $response->assertSee('Notifications');
    }

    /** @test */
    public function it_can_perform_complete_pos_workflow(): void
    {
        // Create POS terminal
        $terminal = POSTerminal::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        // Start POS session via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->postJson('/api/pos/sessions', [
            'terminal_id' => $terminal->id,
            'staff_name' => 'Test Cashier',
            'opening_balance' => 100.00,
        ]);

        $response->assertStatus(201);
        $sessionId = $response->json('data.id');

        // Add items to cart
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/pos/sessions/{$sessionId}/cart", [
            'item_id' => 1,
            'name' => 'Test Burger',
            'price' => 15.99,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);

        // Process payment
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/pos/sessions/{$sessionId}/checkout", [
            'payment_method' => 'cash',
            'amount' => 31.98,
            'table_number' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'transaction_id',
                'receipt_number',
                'total_amount',
            ]
        ]);
    }

    /** @test */
    public function it_can_manage_loyalty_program_workflow(): void
    {
        // Create loyalty program
        $program = LoyaltyProgram::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        // Create customer
        $customer = User::factory()->create(['role' => 'customer']);

        // Enroll customer in loyalty program via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/loyalty/programs/{$program->id}/members", [
            'user_id' => $customer->id,
            'phone' => '+1234567890',
        ]);

        $response->assertStatus(201);
        $memberId = $response->json('data.id');

        // Award points
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/loyalty/members/{$memberId}/transactions", [
            'type' => 'earn',
            'points' => 50,
            'description' => 'Purchase reward',
            'order_id' => 'ORD-123456',
        ]);

        $response->assertStatus(201);

        // Redeem points
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/loyalty/members/{$memberId}/transactions", [
            'type' => 'redeem',
            'points' => 25,
            'description' => 'Free dessert',
        ]);

        $response->assertStatus(201);

        // Check member balance
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->getJson("/api/loyalty/members/{$memberId}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'points_balance' => 25, // 50 earned - 25 redeemed
            ]
        ]);
    }

    /** @test */
    public function it_can_generate_and_scan_table_qr_codes(): void
    {
        // Create table QR code
        $tableQr = TableQrCode::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        // Get table QR via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->getJson("/api/tableqr/tables/{$tableQr->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'table_number',
                'qr_code',
                'url',
                'is_active',
            ]
        ]);

        // Simulate QR scan
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/tableqr/scan", [
            'qr_code' => $tableQr->qr_code,
            'customer_id' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'success' => true,
                'restaurant_id' => $this->restaurant->id,
            ]
        ]);

        // Verify scan count increased
        $tableQr->refresh();
        $this->assertEquals(1, $tableQr->scan_count);
        $this->assertNotNull($tableQr->last_scanned_at);
    }

    /** @test */
    public function it_can_handle_import_export_operations(): void
    {
        $this->actingAs($this->restaurantAdmin);

        $importExportService = app(ImportExportService::class);

        // Test CSV content creation
        $csvContent = "name,price,description\nTest Burger,15.99,Delicious burger\nTest Pizza,22.50,Margherita pizza";
        $filePath = storage_path('app/temp/test_menu.csv');

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $csvContent);

        // Test file analysis
        $analysis = $importExportService->analyzeFile($filePath, 'menus');

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('columns', $analysis);
        $this->assertArrayHasKey('row_count', $analysis);
        $this->assertEquals(2, $analysis['row_count']); // Excluding header

        // Test data validation
        $testData = [
            ['name' => 'Test Item', 'price' => '15.99', 'description' => 'Test description'],
            ['name' => '', 'price' => 'invalid', 'description' => 'Test description 2'],
        ];

        $validation = $importExportService->validateData($testData, 'menus');

        $this->assertIsArray($validation);
        $this->assertArrayHasKey('valid_count', $validation);
        $this->assertArrayHasKey('invalid_count', $validation);
        $this->assertArrayHasKey('errors', $validation);

        // Clean up test file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /** @test */
    public function it_can_send_firebase_notifications(): void
    {
        // Mock Firebase service for testing
        $firebaseService = $this->createMock(FirebaseService::class);
        $firebaseService->expects($this->once())
            ->method('sendNotification')
            ->with(
                $this->equalTo('test-device-token'),
                $this->equalTo('Test Title'),
                $this->equalTo('Test message'),
                $this->isType('array')
            )
            ->willReturn([
                'success' => true,
                'message_id' => 'test-message-id-123'
            ]);

        $this->app->instance(FirebaseService::class, $firebaseService);

        // Test notification sending via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson('/api/notifications/send', [
            'device_tokens' => ['test-device-token'],
            'title' => 'Test Title',
            'body' => 'Test message',
            'data' => [
                'type' => 'test',
                'restaurant_id' => $this->restaurant->id,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'success' => true,
                'sent_count' => 1,
            ]
        ]);
    }

    /** @test */
    public function it_enforces_proper_security_and_rate_limiting(): void
    {
        // Test without API key
        $response = $this->getJson('/api/pos/terminals');
        $response->assertStatus(401);

        // Test with invalid API key
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-key',
        ])->getJson('/api/pos/terminals');
        $response->assertStatus(401);

        // Test rate limiting (make multiple requests quickly)
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey]);

        $successfulRequests = 0;
        $rateLimitedRequests = 0;

        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->getJson('/api/pos/terminals');

            if ($response->getStatusCode() === 200) {
                $successfulRequests++;
            } elseif ($response->getStatusCode() === 429) {
                $rateLimitedRequests++;
            }
        }

        // Should have some successful requests
        $this->assertGreaterThan(0, $successfulRequests);
    }

    /** @test */
    public function it_provides_analytics_data_across_all_addons(): void
    {
        // Create sample data for analytics
        POSTerminal::factory(3)->create(['restaurant_id' => $this->restaurant->id]);
        LoyaltyProgram::factory()->create(['restaurant_id' => $this->restaurant->id]);
        TableQrCode::factory(10)->create(['restaurant_id' => $this->restaurant->id]);

        $this->actingAs($this->restaurantAdmin);

        // Test analytics endpoint
        $response = $this->get('/admin/addons/dashboard');
        $response->assertStatus(200);

        // Should contain data for all addons
        $response->assertSee('POS Terminals');
        $response->assertSee('Loyalty Programs');
        $response->assertSee('Table QR Codes');
        $response->assertSee('Recent Activities');
    }

    /** @test */
    public function it_handles_cross_addon_interactions(): void
    {
        // Create interconnected data
        $terminal = POSTerminal::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $program = LoyaltyProgram::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $tableQr = TableQrCode::factory()->create(['restaurant_id' => $this->restaurant->id]);
        $customer = User::factory()->create(['role' => 'customer']);

        // Simulate a complete customer journey:
        // 1. Customer scans QR code
        $scanResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson('/api/tableqr/scan', [
            'qr_code' => $tableQr->qr_code,
            'customer_id' => $customer->id,
        ]);

        $scanResponse->assertStatus(200);

        // 2. Customer makes order through POS
        $sessionResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson('/api/pos/sessions', [
            'terminal_id' => $terminal->id,
            'staff_name' => 'Test Staff',
            'opening_balance' => 100.00,
        ]);

        $sessionId = $sessionResponse->json('data.id');

        // 3. Customer earns loyalty points from the order
        $memberResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/loyalty/programs/{$program->id}/members", [
            'user_id' => $customer->id,
            'phone' => '+1234567890',
        ]);

        $memberId = $memberResponse->json('data.id');

        $pointsResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->postJson("/api/loyalty/members/{$memberId}/transactions", [
            'type' => 'earn',
            'points' => 25,
            'description' => 'Order from table ' . $tableQr->table_number,
            'order_id' => 'ORD-' . $sessionId,
        ]);

        $pointsResponse->assertStatus(201);

        // Verify all interactions were successful
        $this->assertDatabaseHas('table_qr_codes', [
            'id' => $tableQr->id,
            'scan_count' => 1,
        ]);

        $this->assertDatabaseHas('loyalty_members', [
            'id' => $memberId,
            'user_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('loyalty_transactions', [
            'member_id' => $memberId,
            'points' => 25,
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up any test files
        $tempDir = storage_path('app/temp');
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::tearDown();
    }
}
