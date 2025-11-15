<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Restaurant;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

class WhatsAppIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $vendor;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure WhatsApp for testing
        Config::set('services.whatsapp.enabled', true);
        Config::set('services.whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        Config::set('services.whatsapp.api_token', 'test_token');
        Config::set('services.whatsapp.phone_number_id', '123456789');

        // Create test user (customer)
        $this->user = User::factory()->create([
            'type' => 3, // Customer
            'mobile' => '+1234567890',
        ]);

        // Create test vendor user
        $vendorUser = User::factory()->create([
            'type' => 2, // Vendor
            'mobile' => '+0987654321',
        ]);

        // Create restaurant for vendor
        $this->vendor = Restaurant::factory()->create([
            'user_id' => $vendorUser->id,
            'restaurant_name' => 'Test Restaurant',
            'restaurant_phone' => '+0987654321',
        ]);

        // Create test order
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'vendor_id' => $vendorUser->id,
            'order_number' => 'ORD-TEST-001',
            'grand_total' => 125.00,
            'sub_total' => 125.00,
            'status' => 1, // Pending
        ]);
    }

    /**
     * Test sending WhatsApp text message
     */
    public function test_send_whatsapp_text_message()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [
                    [
                        'input' => '+1234567890',
                        'wa_id' => '1234567890',
                    ],
                ],
                'messages' => [
                    [
                        'id' => 'wamid.test123456',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/whatsapp/send', [
            'phone' => '+1234567890',
            'message' => 'Hello from RestroSaaS!',
            'type' => 'text',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message_id' => 'wamid.test123456',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v18.0/123456789/messages' &&
                   $request['to'] === '+1234567890' &&
                   $request['type'] === 'text';
        });
    }

    /**
     * Test sending order confirmation via WhatsApp
     */
    public function test_send_order_confirmation_whatsapp()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [
                    ['id' => 'wamid.order_conf_123'],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/whatsapp/order-confirmation', [
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'ORD-TEST-001') &&
                   str_contains($request['text']['body'], '125.00');
        });
    }

    /**
     * Test sending order status update via WhatsApp
     */
    public function test_send_order_status_update_whatsapp()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.status_123']],
            ], 200),
        ]);

        $this->order->update(['status' => 4]); // Preparing

        $response = $this->postJson('/api/whatsapp/order-status', [
            'order_id' => $this->order->id,
            'status' => 4, // Preparing
        ]);

        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'preparing') &&
                   str_contains($request['text']['body'], 'ORD-TEST-001');
        });
    }

    /**
     * Test sending WhatsApp template message
     */
    public function test_send_whatsapp_template_message()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template_123']],
            ], 200),
        ]);

        $response = $this->postJson('/api/whatsapp/send-template', [
            'phone' => '+1234567890',
            'template_name' => 'order_confirmation',
            'language' => 'en',
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => 'ORD-TEST-001'],
                        ['type' => 'text', 'text' => '125.00'],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return $request['type'] === 'template' &&
                   $request['template']['name'] === 'order_confirmation';
        });
    }

    /**
     * Test WhatsApp message queue dispatch
     */
    public function test_whatsapp_message_queued()
    {
        Queue::fake();

        $this->postJson('/api/whatsapp/send-async', [
            'phone' => '+1234567890',
            'message' => 'Queued message',
        ]);

        Queue::assertPushed(SendWhatsAppMessageJob::class, function ($job) {
            return $job->phoneNumber === '+1234567890';
        });
    }

    /**
     * Test WhatsApp webhook verification
     */
    public function test_whatsapp_webhook_verification()
    {
        Config::set('services.whatsapp.webhook_verify_token', 'my_verify_token');

        $response = $this->getJson('/webhook/whatsapp', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'my_verify_token',
            'hub_challenge' => 'test_challenge_123',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('test_challenge_123', $response->getContent());
    }

    /**
     * Test WhatsApp webhook verification failure
     */
    public function test_whatsapp_webhook_verification_fails_with_invalid_token()
    {
        Config::set('services.whatsapp.webhook_verify_token', 'my_verify_token');

        $response = $this->getJson('/webhook/whatsapp', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'invalid_token',
            'hub_challenge' => 'test_challenge_123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test receiving WhatsApp message webhook
     */
    public function test_receive_whatsapp_message_webhook()
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => '123456789',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'phone_number_id' => '123456789',
                                ],
                                'messages' => [
                                    [
                                        'from' => '1234567890',
                                        'id' => 'wamid.incoming_123',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Hello',
                                        ],
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/webhook/whatsapp', $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'received']);
    }

    /**
     * Test WhatsApp API authentication failure
     */
    public function test_whatsapp_api_authentication_failure()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid OAuth access token',
                    'type' => 'OAuthException',
                    'code' => 190,
                ],
            ], 401),
        ]);

        $response = $this->postJson('/api/whatsapp/send', [
            'phone' => '+1234567890',
            'message' => 'Test message',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => 'Authentication failed',
        ]);
    }

    /**
     * Test WhatsApp rate limiting
     */
    public function test_whatsapp_rate_limiting()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Rate limit exceeded',
                    'code' => 80007,
                ],
            ], 429),
        ]);

        $response = $this->postJson('/api/whatsapp/send', [
            'phone' => '+1234567890',
            'message' => 'Test message',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'error' => 'Rate limit exceeded',
        ]);
    }

    /**
     * Test WhatsApp disabled configuration
     */
    public function test_whatsapp_disabled()
    {
        Config::set('services.whatsapp.enabled', false);

        $response = $this->postJson('/api/whatsapp/send', [
            'phone' => '+1234567890',
            'message' => 'Test message',
        ]);

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'error' => 'WhatsApp service is disabled',
        ]);
    }

    /**
     * Test sending WhatsApp to invalid phone number
     */
    public function test_send_whatsapp_to_invalid_phone()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid phone number',
                    'code' => 100,
                ],
            ], 400),
        ]);

        $response = $this->postJson('/api/whatsapp/send', [
            'phone' => 'invalid_phone',
            'message' => 'Test message',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'error' => 'Invalid phone number',
        ]);
    }

    /**
     * Test bulk WhatsApp message sending
     */
    public function test_bulk_whatsapp_messages()
    {
        Queue::fake();

        $phones = ['+1111111111', '+2222222222', '+3333333333'];

        $response = $this->postJson('/api/whatsapp/send-bulk', [
            'phones' => $phones,
            'message' => 'Bulk message',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'queued' => 3,
        ]);

        Queue::assertPushed(SendWhatsAppMessageJob::class, 3);
    }

    /**
     * Test WhatsApp message status tracking
     */
    public function test_whatsapp_message_status_webhook()
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'statuses' => [
                                    [
                                        'id' => 'wamid.test_123',
                                        'status' => 'delivered',
                                        'timestamp' => '1234567890',
                                        'recipient_id' => '1234567890',
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/webhook/whatsapp', $payload);

        $response->assertStatus(200);

        // Verify status was recorded in database
        $this->assertDatabaseHas('whatsapp_messages', [
            'message_id' => 'wamid.test_123',
            'status' => 'delivered',
        ]);
    }

    /**
     * Test WhatsApp opt-out handling
     */
    public function test_whatsapp_opt_out_handling()
    {
        $response = $this->postJson('/api/whatsapp/opt-out', [
            'phone' => '+1234567890',
        ]);

        $response->assertStatus(200);

        // Verify user is marked as opted out
        $this->assertDatabaseHas('users', [
            'mobile' => '+1234567890',
            'whatsapp_opt_out' => true,
        ]);

        // Try to send message to opted-out user
        $sendResponse = $this->postJson('/api/whatsapp/send', [
            'phone' => '+1234567890',
            'message' => 'Test message',
        ]);

        $sendResponse->assertStatus(403);
        $sendResponse->assertJson([
            'success' => false,
            'error' => 'User has opted out of WhatsApp messages',
        ]);
    }
}
