<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $customer;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor
        $this->vendor = User::factory()->create([
            'type' => 2,
            'is_available' => 1,
        ]);

        // Create customer
        $this->customer = User::factory()->create([
            'type' => 3,
            'vendor_id' => $this->vendor->id,
        ]);

        // Create test order
        $this->order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'grand_total' => 50.00,
            'status' => 1, // Pending
            'transaction_type' => 1, // Default payment type
        ]);

        // Create payment methods
        Payment::factory()->create([
            'vendor_id' => $this->vendor->id,
            'payment_name' => 'Stripe',
            'payment_type' => '2',
            'is_available' => 1,
            'is_activate' => 1,
        ]);

        Payment::factory()->create([
            'vendor_id' => $this->vendor->id,
            'payment_name' => 'PayPal',
            'payment_type' => '3',
            'is_available' => 1,
            'is_activate' => 1,
        ]);

        Payment::factory()->create([
            'vendor_id' => $this->vendor->id,
            'payment_name' => 'COD',
            'payment_type' => '1',
            'is_available' => 1,
            'is_activate' => 1,
        ]);
    }

    /** @test */
    public function test_can_process_cash_on_delivery_payment()
    {
        $this->actingAs($this->customer);

        $response = $this->post(route('order.place'), [
            'payment_type' => '1', // COD
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(302);

        $this->order->refresh();
        $this->assertEquals(1, $this->order->transaction_type);
        $this->assertEquals(1, $this->order->status); // Still pending
    }

    /** @test */
    public function test_can_initiate_stripe_payment()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'pi_test_123456',
                'client_secret' => 'pi_test_123456_secret_test',
                'status' => 'requires_payment_method',
            ], 200),
        ]);

        $response = $this->post(route('payment.stripe.intent'), [
            'order_id' => $this->order->id,
            'amount' => 5000, // $50.00 in cents
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'client_secret',
        ]);
    }

    /** @test */
    public function test_can_confirm_stripe_payment()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'pi_test_123456',
                'status' => 'succeeded',
                'amount' => 5000,
                'currency' => 'usd',
            ], 200),
        ]);

        $response = $this->post(route('payment.stripe.confirm'), [
            'order_id' => $this->order->id,
            'payment_intent_id' => 'pi_test_123456',
        ]);

        $response->assertStatus(302);

        $this->order->refresh();
        $this->assertEquals('2', $this->order->transaction_type);
        $this->assertEquals(2, $this->order->status); // Confirmed
    }

    /** @test */
    public function test_can_create_paypal_order()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test_access_token',
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL123456',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://paypal.com/approve'],
                ],
            ], 201),
        ]);

        $service = new PayPalService();
        $result = $service->createExpressCheckout([
            'amount' => 50.00,
            'currency' => 'USD',
            'description' => 'Order #' . $this->order->order_number,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('PAYPAL123456', $result['data']['id']);
    }

    /** @test */
    public function test_can_capture_paypal_payment()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test_access_token',
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'id' => 'PAYPAL123456',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                ['id' => 'CAPTURE123', 'status' => 'COMPLETED']
                            ]
                        ]
                    ]
                ],
            ], 201),
        ]);

        $response = $this->post(route('payment.paypal.capture'), [
            'order_id' => $this->order->id,
            'paypal_order_id' => 'PAYPAL123456',
        ]);

        $response->assertStatus(302);

        $this->order->refresh();
        $this->assertEquals('3', $this->order->transaction_type);
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_handles_failed_stripe_payment()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'error' => [
                    'message' => 'Your card was declined.',
                ],
            ], 402),
        ]);

        $response = $this->post(route('payment.stripe.confirm'), [
            'order_id' => $this->order->id,
            'payment_intent_id' => 'pi_test_failed',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->order->refresh();
        $this->assertEquals(1, $this->order->status); // Still pending
    }

    /** @test */
    public function test_handles_failed_paypal_payment()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test_access_token',
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'name' => 'UNPROCESSABLE_ENTITY',
                'message' => 'The requested action could not be performed',
            ], 422),
        ]);

        $response = $this->post(route('payment.paypal.capture'), [
            'order_id' => $this->order->id,
            'paypal_order_id' => 'PAYPAL_INVALID',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_can_process_refund_for_stripe()
    {
        $this->actingAs($this->vendor);

        $this->order->update([
            'payment_type' => '2',
            'status' => 2,
            'payment_id' => 'pi_test_123456',
        ]);

        Http::fake([
            'api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_test_123456',
                'status' => 'succeeded',
                'amount' => 5000,
            ], 200),
        ]);

        $response = $this->post(route('payment.refund'), [
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'reason' => 'Customer request',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_can_process_partial_refund()
    {
        $this->actingAs($this->vendor);

        $this->order->update([
            'payment_type' => '2',
            'status' => 2,
            'payment_id' => 'pi_test_123456',
            'grand_total' => 100.00,
        ]);

        Http::fake([
            'api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_test_partial',
                'status' => 'succeeded',
                'amount' => 3000, // $30.00 partial refund
            ], 200),
        ]);

        $response = $this->post(route('payment.refund'), [
            'order_id' => $this->order->id,
            'amount' => 30.00,
            'reason' => 'Partial refund - damaged item',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_validates_payment_amount()
    {
        $this->actingAs($this->customer);

        $response = $this->post(route('payment.stripe.intent'), [
            'order_id' => $this->order->id,
            'amount' => 0, // Invalid amount
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function test_validates_payment_method_availability()
    {
        Payment::where('vendor_id', $this->vendor->id)
            ->where('transaction_type', '2')
            ->update(['is_available' => 0]);

        $this->actingAs($this->customer);

        $response = $this->post(route('payment.stripe.intent'), [
            'order_id' => $this->order->id,
            'amount' => 5000,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Payment method not available']);
    }

    /** @test */
    public function test_handles_stripe_webhook_payment_succeeded()
    {
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_webhook',
                    'amount' => 5000,
                    'status' => 'succeeded',
                    'metadata' => [
                        'order_id' => $this->order->id,
                    ],
                ],
            ],
        ];

        $response = $this->post(route('webhook.stripe'), $payload);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_handles_stripe_webhook_payment_failed()
    {
        $payload = [
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_webhook_failed',
                    'metadata' => [
                        'order_id' => $this->order->id,
                    ],
                ],
            ],
        ];

        $response = $this->post(route('webhook.stripe'), $payload);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals(5, $this->order->status); // Cancelled
    }

    /** @test */
    public function test_handles_paypal_webhook_payment_completed()
    {
        $payload = [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE123',
                'amount' => [
                    'value' => '50.00',
                    'currency_code' => 'USD',
                ],
                'custom_id' => $this->order->id,
            ],
        ];

        $response = $this->post(route('webhook.paypal'), $payload);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_prevents_duplicate_payment_processing()
    {
        $this->order->update([
            'status' => 2,
            'payment_type' => '2',
            'payment_id' => 'pi_already_paid',
        ]);

        $this->actingAs($this->customer);

        $response = $this->post(route('payment.stripe.confirm'), [
            'order_id' => $this->order->id,
            'payment_intent_id' => 'pi_test_duplicate',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Order already paid']);
    }

    /** @test */
    public function test_logs_payment_transactions()
    {
        $this->actingAs($this->customer);

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'pi_test_logging',
                'status' => 'succeeded',
            ], 200),
        ]);

        $this->post(route('payment.stripe.confirm'), [
            'order_id' => $this->order->id,
            'payment_intent_id' => 'pi_test_logging',
        ]);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'payment_type' => '2',
            'status' => 'success',
        ]);
    }

    /** @test */
    public function test_can_retrieve_payment_methods_for_vendor()
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('payment.methods', ['vendor_id' => $this->vendor->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(3); // COD, Stripe, PayPal
        $response->assertJsonFragment(['payment_name' => 'Stripe']);
        $response->assertJsonFragment(['payment_name' => 'PayPal']);
    }

    /** @test */
    public function test_only_returns_available_payment_methods()
    {
        Payment::where('vendor_id', $this->vendor->id)
            ->where('transaction_type', '2')
            ->update(['is_available' => 0]);

        $this->actingAs($this->customer);

        $response = $this->get(route('payment.methods', ['vendor_id' => $this->vendor->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Only COD and PayPal
        $response->assertJsonMissing(['payment_name' => 'Stripe']);
    }
}
