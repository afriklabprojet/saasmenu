<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $customer;
    protected $restaurant;
    protected $order;
    protected $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer@test.com'
        ]);

        $this->restaurant = Restaurant::factory()->create([
            'restaurant_name' => 'Test Restaurant',
            'is_active' => 1
        ]);

        $this->order = Order::factory()->create([
            'vendor_id' => $this->restaurant->user_id,
            'customer_id' => $this->customer->id,
            'total_amount' => 50.00,
            'status' => 'pending'
        ]);

        $this->paymentMethod = PaymentMethod::factory()->create([
            'name' => 'Credit Card',
            'type' => 'stripe',
            'is_active' => 1
        ]);
    }

    /**
     * Test de traitement de paiement réussi
     */
    public function test_successful_payment_processing()
    {
        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 50.00,
            'currency' => 'USD',
            'payment_token' => 'test_token_' . uniqid()
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'transaction_id',
                        'status',
                        'amount',
                        'payment_method'
                    ]
                ]);

        // Vérifier que la transaction est enregistrée
        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'status' => 'completed'
        ]);

        // Vérifier que le statut de la commande est mis à jour
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->payment_status);
    }

    /**
     * Test d'échec de paiement avec montant incorrect
     */
    public function test_payment_fails_with_incorrect_amount()
    {
        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 30.00, // Montant incorrect
            'currency' => 'USD',
            'payment_token' => 'test_token_' . uniqid()
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Test de validation des données de paiement
     */
    public function test_payment_validation_errors()
    {
        Sanctum::actingAs($this->customer);

        $invalidData = [
            'order_id' => 999, // Commande inexistante
            'payment_method_id' => 999, // Méthode inexistante
            'amount' => -10, // Montant négatif
            'currency' => 'INVALID' // Devise invalide
        ];

        $response = $this->postJson('/api/payments/process', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors([
                    'order_id',
                    'payment_method_id',
                    'amount',
                    'currency'
                ]);
    }

    /**
     * Test de sécurité - Un client ne peut payer que ses propres commandes
     */
    public function test_customer_cannot_pay_other_customer_order()
    {
        $otherCustomer = User::factory()->create(['type' => 'customer']);
        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'total_amount' => 30.00
        ]);

        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $otherOrder->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 30.00,
            'currency' => 'USD',
            'payment_token' => 'test_token_' . uniqid()
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test de remboursement
     */
    public function test_successful_refund_processing()
    {
        // Créer une transaction complétée
        $transaction = Transaction::factory()->create([
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'status' => 'completed',
            'payment_method' => 'stripe'
        ]);

        Sanctum::actingAs($this->customer);

        $refundData = [
            'transaction_id' => $transaction->id,
            'amount' => 50.00,
            'reason' => 'Customer request'
        ];

        $response = $this->postJson('/api/payments/refund', $refundData);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'refund_id',
                        'status',
                        'amount'
                    ]
                ]);

        // Vérifier que le remboursement est enregistré
        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'amount' => -50.00, // Montant négatif pour le remboursement
            'status' => 'refunded'
        ]);
    }

    /**
     * Test de remboursement partiel
     */
    public function test_partial_refund_processing()
    {
        $transaction = Transaction::factory()->create([
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'status' => 'completed'
        ]);

        Sanctum::actingAs($this->customer);

        $refundData = [
            'transaction_id' => $transaction->id,
            'amount' => 20.00, // Remboursement partiel
            'reason' => 'Partial refund'
        ];

        $response = $this->postJson('/api/payments/refund', $refundData);

        $response->assertStatus(Response::HTTP_OK);

        // Vérifier le remboursement partiel
        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'amount' => -20.00,
            'status' => 'refunded'
        ]);
    }

    /**
     * Test de webhook de paiement
     */
    public function test_payment_webhook_processing()
    {
        $webhookData = [
            'type' => 'payment.success',
            'data' => [
                'transaction_id' => 'stripe_tx_' . uniqid(),
                'order_id' => $this->order->id,
                'amount' => 50.00,
                'status' => 'completed'
            ]
        ];

        $response = $this->postJson('/api/webhooks/payment', $webhookData);

        $response->assertStatus(Response::HTTP_OK);

        // Vérifier que la transaction webhook est traitée
        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'status' => 'completed'
        ]);
    }

    /**
     * Test de sécurité du webhook avec signature invalide
     */
    public function test_webhook_rejects_invalid_signature()
    {
        $webhookData = [
            'type' => 'payment.success',
            'data' => [
                'transaction_id' => 'malicious_tx',
                'order_id' => $this->order->id,
                'amount' => 1000.00 // Montant suspect
            ]
        ];

        // Webhook sans signature valide
        $response = $this->postJson('/api/webhooks/payment', $webhookData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Vérifier qu'aucune transaction malveillante n'est créée
        $this->assertDatabaseMissing('transactions', [
            'order_id' => $this->order->id,
            'amount' => 1000.00
        ]);
    }

    /**
     * Test de traitement des paiements récurrents
     */
    public function test_recurring_payment_processing()
    {
        $subscription = [
            'customer_id' => $this->customer->id,
            'plan_id' => 'premium_monthly',
            'amount' => 29.99,
            'currency' => 'USD',
            'interval' => 'monthly'
        ];

        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/subscriptions', $subscription);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'subscription_id',
                        'status',
                        'next_billing_date'
                    ]
                ]);
    }

    /**
     * Test de gestion des échecs de paiement
     */
    public function test_payment_failure_handling()
    {
        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 50.00,
            'currency' => 'USD',
            'payment_token' => 'invalid_token' // Token qui va échouer
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_PAYMENT_REQUIRED)
                ->assertJson([
                    'success' => false,
                    'error' => 'Payment failed'
                ]);

        // Vérifier que la transaction d'échec est enregistrée
        $this->assertDatabaseHas('transactions', [
            'order_id' => $this->order->id,
            'status' => 'failed'
        ]);
    }

    /**
     * Test de protection contre la double facturation
     */
    public function test_duplicate_payment_prevention()
    {
        // Créer une transaction déjà complétée
        Transaction::factory()->create([
            'order_id' => $this->order->id,
            'amount' => 50.00,
            'status' => 'completed'
        ]);

        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 50.00,
            'currency' => 'USD',
            'payment_token' => 'test_token_' . uniqid()
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_CONFLICT)
                ->assertJson([
                    'success' => false,
                    'error' => 'Payment already processed'
                ]);
    }

    /**
     * Test de calcul des frais de transaction
     */
    public function test_transaction_fee_calculation()
    {
        Sanctum::actingAs($this->customer);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_token' => 'test_token_' . uniqid()
        ];

        $response = $this->postJson('/api/payments/process', $paymentData);

        $response->assertStatus(Response::HTTP_OK);

        $transaction = Transaction::where('order_id', $this->order->id)->first();

        // Vérifier que les frais sont calculés (par exemple 2.9% + 0.30)
        $expectedFee = round((100.00 * 0.029) + 0.30, 2);
        $this->assertEquals($expectedFee, $transaction->processing_fee);
    }

    /**
     * Test de génération de reçu de paiement
     */
    public function test_payment_receipt_generation()
    {
        $transaction = Transaction::factory()->create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'amount' => 50.00,
            'status' => 'completed'
        ]);

        Sanctum::actingAs($this->customer);

        $response = $this->getJson("/api/payments/{$transaction->id}/receipt");

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'receipt_id',
                        'transaction_id',
                        'amount',
                        'date',
                        'customer_details',
                        'order_details'
                    ]
                ]);
    }
}
