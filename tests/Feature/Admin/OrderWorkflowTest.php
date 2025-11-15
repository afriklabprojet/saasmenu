<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderWorkflowTest extends TestCase
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

        // Create order
        $this->order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1,
            'grand_total' => 100.00,
        ]);
    }

    /** @test */
    public function test_order_is_created_with_default_status()
    {
        $this->assertEquals(1, $this->order->status);
        $this->assertNotNull($this->order->order_number);
    }

    /** @test */
    public function test_order_can_transition_to_confirmed()
    {
        $this->order->update(['status' => 2]);
        
        $this->order->refresh();
        $this->assertEquals(2, $this->order->status);
    }

    /** @test */
    public function test_order_can_transition_to_cancelled()
    {
        $this->order->update(['status' => 3]);
        
        $this->order->refresh();
        $this->assertEquals(3, $this->order->status);
    }

    /** @test */
    public function test_order_can_transition_to_preparing()
    {
        $this->order->update(['status' => 2]);
        $this->order->update(['status' => 4]);
        
        $this->order->refresh();
        $this->assertEquals(4, $this->order->status);
    }

    /** @test */
    public function test_order_can_transition_to_ready()
    {
        $this->order->update(['status' => 5]);
        
        $this->order->refresh();
        $this->assertEquals(5, $this->order->status);
    }

    /** @test */
    public function test_order_can_transition_to_on_delivery()
    {
        $this->order->update(['status' => 6]);
        
        $this->order->refresh();
        $this->assertEquals(6, $this->order->status);
    }

    /** @test */
    public function test_order_can_transition_to_delivered()
    {
        $this->order->update(['status' => 7]);
        
        $this->order->refresh();
        $this->assertEquals(7, $this->order->status);
    }

    /** @test */
    public function test_order_belongs_to_vendor()
    {
        $this->assertEquals($this->vendor->id, $this->order->vendor_id);
    }

    /** @test */
    public function test_order_belongs_to_customer()
    {
        $this->assertEquals($this->customer->id, $this->order->user_id);
    }

    /** @test */
    public function test_order_has_unique_order_number()
    {
        $order2 = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $this->assertNotEquals($this->order->order_number, $order2->order_number);
    }

    /** @test */
    public function test_order_has_grand_total()
    {
        $this->assertNotNull($this->order->grand_total);
        $this->assertGreaterThan(0, $this->order->grand_total);
    }

    /** @test */
    public function test_can_create_order_with_billing_address()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_postal_code' => '10001',
        ]);

        $this->assertEquals('123 Main St', $order->billing_address);
        $this->assertEquals('New York', $order->billing_city);
    }

    /** @test */
    public function test_can_create_order_with_shipping_address()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'shipping_address' => '456 Oak Ave',
            'shipping_city' => 'Los Angeles',
            'shipping_postal_code' => '90001',
        ]);

        $this->assertEquals('456 Oak Ave', $order->shipping_address);
        $this->assertEquals('Los Angeles', $order->shipping_city);
    }

    /** @test */
    public function test_multiple_orders_can_exist()
    {
        Order::factory()->count(5)->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $this->assertGreaterThanOrEqual(6, Order::count());
    }

    /** @test */
    public function test_order_can_be_filtered_by_status()
    {
        Order::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'status' => 2,
        ]);

        $confirmedOrders = Order::where('status', 2)->get();
        $this->assertGreaterThanOrEqual(3, $confirmedOrders->count());
    }

    /** @test */
    public function test_vendor_can_have_multiple_orders()
    {
        Order::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $vendorOrders = Order::where('vendor_id', $this->vendor->id)->get();
        $this->assertGreaterThanOrEqual(4, $vendorOrders->count());
    }

    /** @test */
    public function test_customer_can_have_multiple_orders()
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $customerOrders = Order::where('user_id', $this->customer->id)->get();
        $this->assertGreaterThanOrEqual(3, $customerOrders->count());
    }

    /** @test */
    public function test_order_status_is_integer()
    {
        $this->assertIsInt($this->order->status);
    }

    /** @test */
    public function test_order_grand_total_is_float()
    {
        $this->assertIsFloat($this->order->grand_total);
    }

    /** @test */
    public function test_can_update_order_notes()
    {
        $this->order->update(['notes' => 'Special instructions']);
        
        $this->order->refresh();
        $this->assertEquals('Special instructions', $this->order->notes);
    }

    /** @test */
    public function test_order_has_timestamps()
    {
        $this->assertNotNull($this->order->created_at);
        $this->assertNotNull($this->order->updated_at);
    }

    /** @test */
    public function test_can_delete_order()
    {
        $orderId = $this->order->id;
        $this->order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $orderId]);
    }

    /** @test */
    public function test_order_transaction_type_can_be_set()
    {
        $this->order->update(['transaction_type' => 2]);
        
        $this->order->refresh();
        $this->assertEquals(2, $this->order->transaction_type);
    }

    /** @test */
    public function test_order_transaction_id_can_be_set()
    {
        $this->order->update(['transaction_id' => 'TXN123456']);
        
        $this->order->refresh();
        $this->assertEquals('TXN123456', $this->order->transaction_id);
    }
}
