<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FirebaseService;
use App\Models\User;
use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Mockery;

class FirebaseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $firebaseService;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        
        // Configuration Firebase de test
        Config::set('firebase.enabled', true);
        Config::set('firebase.project_id', 'test-project');
        Config::set('firebase.server_key', 'test-server-key');
        Config::set('firebase.web_api_key', 'test-web-key');
        
        $this->firebaseService = new FirebaseService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_checks_if_firebase_is_configured()
    {
        $this->assertTrue($this->firebaseService->isConfigured());
        
        // Test avec configuration manquante
        Config::set('firebase.project_id', '');
        $this->assertFalse($this->firebaseService->isConfigured());
    }

    /** @test */
    public function it_can_register_device_token()
    {
        $token = 'test-device-token-123';
        $deviceInfo = [
            'platform' => 'android',
            'device_type' => 'mobile',
            'app_version' => '1.0.0'
        ];
        
        $result = $this->firebaseService->registerDeviceToken($this->user->id, $token, $deviceInfo);
        
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->user->id,
            'token' => $token,
            'platform' => 'android'
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_device_tokens()
    {
        $token = 'duplicate-token-123';
        
        // Enregistrer le token une première fois
        DeviceToken::create([
            'user_id' => $this->user->id,
            'token' => $token,
            'platform' => 'ios',
            'device_type' => 'mobile',
            'is_active' => true
        ]);
        
        // Essayer d'enregistrer le même token
        $result = $this->firebaseService->registerDeviceToken($this->user->id, $token, [
            'platform' => 'ios',
            'device_type' => 'mobile'
        ]);
        
        $this->assertTrue($result['success']);
        
        // Vérifier qu'il n'y a toujours qu'un seul enregistrement
        $this->assertEquals(1, DeviceToken::where('token', $token)->count());
    }

    /** @test */
    public function it_can_send_notification_to_single_token()
    {
        Http::fake([
            'fcm.googleapis.com/*' => Http::response(['success' => 1], 200)
        ]);
        
        $token = 'test-token-123';
        $notification = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'token' => $token
        ];
        
        $result = $this->firebaseService->sendNotification($notification);
        
        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'fcm.googleapis.com');
        });
    }

    /** @test */
    public function it_can_send_notification_to_multiple_tokens()
    {
        Http::fake([
            'fcm.googleapis.com/*' => Http::response(['success' => 2], 200)
        ]);
        
        $tokens = ['token-1', 'token-2'];
        $notification = [
            'title' => 'Bulk Notification',
            'body' => 'This is a bulk notification',
            'tokens' => $tokens
        ];
        
        $result = $this->firebaseService->sendBulkNotification($notification);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['successful_sends']);
    }

    /** @test */
    public function it_handles_firebase_api_errors()
    {
        Http::fake([
            'fcm.googleapis.com/*' => Http::response(['error' => 'Invalid token'], 400)
        ]);
        
        $notification = [
            'title' => 'Test',
            'body' => 'Test',
            'token' => 'invalid-token'
        ];
        
        $result = $this->firebaseService->sendNotification($notification);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /** @test */
    public function it_can_subscribe_to_topic()
    {
        Http::fake([
            'iid.googleapis.com/*' => Http::response(['results' => [['success' => true]]], 200)
        ]);
        
        $tokens = ['token-1', 'token-2'];
        $topic = 'promotions';
        
        $result = $this->firebaseService->subscribeToTopic($tokens, $topic);
        
        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) use ($topic) {
            return str_contains($request->url(), 'iid.googleapis.com') &&
                   str_contains($request->body(), $topic);
        });
    }

    /** @test */
    public function it_can_unsubscribe_from_topic()
    {
        Http::fake([
            'iid.googleapis.com/*' => Http::response(['results' => [['success' => true]]], 200)
        ]);
        
        $tokens = ['token-1'];
        $topic = 'promotions';
        
        $result = $this->firebaseService->unsubscribeFromTopic($tokens, $topic);
        
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_validates_notification_data()
    {
        // Test notification sans titre
        $notification = [
            'body' => 'Test body',
            'token' => 'test-token'
        ];
        
        $result = $this->firebaseService->sendNotification($notification);
        
        $this->assertFalse($result['success']);
        $this->assertStringContains('title', $result['error']);
    }

    /** @test */
    public function it_can_send_notification_to_topic()
    {
        Http::fake([
            'fcm.googleapis.com/*' => Http::response(['message_id' => '123'], 200)
        ]);
        
        $notification = [
            'title' => 'Topic Notification',
            'body' => 'This goes to a topic',
            'topic' => 'all_users'
        ];
        
        $result = $this->firebaseService->sendToTopic($notification);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message_id', $result);
    }

    /** @test */
    public function it_can_cleanup_expired_tokens()
    {
        // Créer des tokens expirés
        DeviceToken::factory()->count(3)->create([
            'last_activity' => now()->subDays(200),
            'is_active' => true
        ]);
        
        // Créer des tokens actifs
        DeviceToken::factory()->count(2)->create([
            'last_activity' => now()->subDays(10),
            'is_active' => true
        ]);
        
        $result = $this->firebaseService->cleanupExpiredTokens();
        
        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['cleaned_count']);
        
        // Vérifier que les tokens expirés sont marqués comme inactifs
        $this->assertEquals(3, DeviceToken::where('is_active', false)->count());
        $this->assertEquals(2, DeviceToken::where('is_active', true)->count());
    }

    /** @test */
    public function it_can_get_user_devices()
    {
        // Créer des tokens pour l'utilisateur
        DeviceToken::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_active' => true
        ]);
        
        // Créer un token pour un autre utilisateur
        DeviceToken::factory()->create([
            'user_id' => User::factory()->create()->id,
            'is_active' => true
        ]);
        
        $devices = $this->firebaseService->getUserDevices($this->user->id);
        
        $this->assertEquals(2, $devices->count());
        $this->assertEquals($this->user->id, $devices->first()->user_id);
    }

    /** @test */
    public function it_tracks_notification_analytics()
    {
        $notificationId = 123;
        
        // Test ouverture de notification
        $result = $this->firebaseService->trackNotificationOpened($notificationId, 'test-token');
        $this->assertTrue($result['success']);
        
        // Test clic sur notification
        $result = $this->firebaseService->trackNotificationClicked($notificationId, 'test-token');
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_handles_service_unavailable()
    {
        // Désactiver Firebase
        Config::set('firebase.enabled', false);
        
        $result = $this->firebaseService->sendNotification([
            'title' => 'Test',
            'body' => 'Test',
            'token' => 'test-token'
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertStringContains('disabled', $result['error']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}