<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
            'transaction_type' => 1,
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
    public function test_order_can_be_created_with_pending_status()
    {
        $this->assertNotNull($this->order);
        $this->assertEquals(1, $this->order->status);
        $this->assertEquals(50.00, $this->order->grand_total);
    }

    /** @test */
    public function test_can_update_order_payment_to_cod()
    {
        $this->order->update(['transaction_type' => 1]);
        
        $this->order->refresh();
        $this->assertEquals(1, $this->order->transaction_type);
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'transaction_type' => 1,
        ]);
    }

    /** @test */
    public function test_can_update_order_with_stripe_payment()
    {
        $this->order->update([
            'transaction_type' => 2,
            'transaction_id' => 'pi_test_123456',
            'status' => 2,
        ]);

        $this->order->refresh();
        $this->assertEquals(2, $this->order->transaction_type);
        $this->assertEquals('pi_test_123456', $this->order->transaction_id);
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_stripe_payment_method_exists()
    {
        $stripePayment = Payment::where('vendor_id', $this->vendor->id)
            ->where('payment_type', '2')
            ->first();

        $this->assertNotNull($stripePayment);
        $this->assertEquals('Stripe', $stripePayment->payment_name);
        $this->assertEquals(1, $stripePayment->is_available);
    }

    /** @test */
    public function test_can_update_order_with_paypal_payment()
    {
        $this->order->update([
            'transaction_type' => 3,
            'transaction_id' => 'PAYPAL123456',
            'status' => 2,
        ]);

        $this->order->refresh();
        $this->assertEquals(3, $this->order->transaction_type);
        $this->assertEquals('PAYPAL123456', $this->order->transaction_id);
    }

    /** @test */
    public function test_paypal_payment_method_exists()
    {
        $paypalPayment = Payment::where('vendor_id', $this->vendor->id)
            ->where('payment_type', '3')
            ->first();

        $this->assertNotNull($paypalPayment);
        $this->assertEquals('PayPal', $paypalPayment->payment_name);
    }

    /** @test */
    public function test_cod_payment_method_exists()
    {
        $codPayment = Payment::where('vendor_id', $this->vendor->id)
            ->where('payment_type', '1')
            ->first();

        $this->assertNotNull($codPayment);
        $this->assertEquals('COD', $codPayment->payment_name);
    }

    /** @test */
    public function test_order_status_can_be_confirmed()
    {
        $this->order->update(['status' => 2]);
        
        $this->order->refresh();
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_order_status_can_be_cancelled()
    {
        $this->order->update(['status' => 3]);
        
        $this->order->refresh();
        $this->assertEquals(3, $this->order->status);
    }

    /** @test */
    public function test_order_status_can_be_delivered()
    {
        $this->order->update(['status' => 5]);
        
        $this->order->refresh();
        $this->assertEquals(5, $this->order->status);
    }

    /** @test */
    public function test_can_get_all_available_payment_methods()
    {
        $payments = Payment::where('vendor_id', $this->vendor->id)
            ->where('is_available', 1)
            ->get();

        $this->assertCount(3, $payments);
    }

    /** @test */
    public function test_can_disable_payment_method()
    {
        $payment = Payment::where('vendor_id', $this->vendor->id)
            ->where('payment_type', '2')
            ->first();

        $payment->update(['is_available' => 0]);

        $this->assertEquals(0, $payment->is_available);
    }

    /** @test */
    public function test_order_belongs_to_vendor()
    {
        $this->assertEquals($this->vendor->id, $this->order->vendor_id);
        $this->assertNotNull($this->order->vendorinfo);
    }

    /** @test */
    public function test_order_belongs_to_customer()
    {
        $this->assertEquals($this->customer->id, $this->order->user_id);
    }

    /** @test */
    public function test_can_update_order_transaction_id()
    {
        $this->order->update(['transaction_id' => 'TXN_12345']);
        
        $this->order->refresh();
        $this->assertEquals('TXN_12345', $this->order->transaction_id);
    }

    /** @test */
    public function test_payment_method_has_correct_structure()
    {
        $payment = Payment::where('vendor_id', $this->vendor->id)->first();

        $this->assertNotNull($payment->payment_name);
        $this->assertNotNull($payment->payment_type);
        $this->assertIsInt($payment->is_available);
        $this->assertIsInt($payment->is_activate);
    }

    /** @test */
    public function test_order_has_correct_financial_fields()
    {
        $this->assertNotNull($this->order->grand_total);
        $this->assertIsFloat($this->order->grand_total);
        $this->assertGreaterThan(0, $this->order->grand_total);
    }

    /** @test */
    public function test_can_filter_active_payment_methods()
    {
        $activePayments = Payment::where('vendor_id', $this->vendor->id)
            ->where('is_activate', 1)
            ->get();

        $this->assertGreaterThan(0, $activePayments->count());
    }

    /** @test */
    public function test_multiple_orders_can_exist_for_vendor()
    {
        Order::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $vendorOrders = Order::where('vendor_id', $this->vendor->id)->get();
        $this->assertGreaterThanOrEqual(4, $vendorOrders->count());
    }

    /** @test */
    public function test_order_number_is_unique()
    {
        $order2 = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $this->assertNotEquals($this->order->order_number, $order2->order_number);
    }
}
