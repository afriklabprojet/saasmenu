<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Item;
use App\Models\Cart;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $customer;
    protected $item;

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

        // Create test item
        $this->item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'item_price' => 25.00,
            'qty' => 100,
            'is_available' => 1,
        ]);
    }

    /** @test */
    public function test_complete_order_workflow_from_cart_to_delivery()
    {
        $this->actingAs($this->customer);

        // Step 1: Add item to cart
        $response = $this->post(route('cart.add'), [
            'item_id' => $this->item->id,
            'qty' => 2,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'qty' => 2,
        ]);

        // Step 2: Create address
        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
            'is_default' => 1,
        ]);

        // Step 3: Place order
        $response = $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1', // COD
            'delivery_type' => '1', // Delivery
            'notes' => 'Test order',
        ]);
        $response->assertStatus(302);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->status); // Pending

        // Step 4: Vendor confirms order
        $this->actingAs($this->vendor);
        $response = $this->post(route('order.confirm', $order->id));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(2, $order->status); // Confirmed

        // Step 5: Vendor marks as preparing
        $response = $this->post(route('order.preparing', $order->id));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(3, $order->status); // Preparing

        // Step 6: Vendor marks as ready
        $response = $this->post(route('order.ready', $order->id));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(4, $order->status); // Ready

        // Step 7: Vendor assigns delivery
        $response = $this->post(route('order.assign_delivery', $order->id), [
            'driver_id' => User::factory()->create(['type' => 4])->id,
        ]);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(5, $order->status); // Out for delivery

        // Step 8: Mark as delivered
        $response = $this->post(route('order.delivered', $order->id));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(6, $order->status); // Delivered
    }

    /** @test */
    public function test_order_creation_from_cart()
    {
        $this->actingAs($this->customer);

        // Add multiple items to cart
        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
            'qty' => 2,
            'price' => 25.00,
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $response->assertStatus(302);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(50.00, $order->grand_total); // 2 x $25

        // Cart should be cleared
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function test_order_requires_valid_address()
    {
        $this->actingAs($this->customer);

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->post(route('order.place'), [
            'address_id' => 99999, // Invalid address
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address_id']);
    }

    /** @test */
    public function test_order_validates_cart_not_empty()
    {
        $this->actingAs($this->customer);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Cart is empty']);
    }

    /** @test */
    public function test_order_validates_item_availability()
    {
        $this->actingAs($this->customer);

        $this->item->update(['is_available' => 0]);

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Some items are not available']);
    }

    /** @test */
    public function test_order_validates_sufficient_stock()
    {
        $this->actingAs($this->customer);

        $this->item->update(['qty' => 1]);

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
            'qty' => 5, // More than available
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Insufficient stock']);
    }

    /** @test */
    public function test_order_decrements_item_stock()
    {
        $this->actingAs($this->customer);

        $initialStock = $this->item->qty;

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
            'qty' => 3,
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
        ]);

        $this->item->refresh();
        $this->assertEquals($initialStock - 3, $this->item->qty);
    }

    /** @test */
    public function test_vendor_can_confirm_pending_order()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1, // Pending
        ]);

        $this->actingAs($this->vendor);

        $response = $this->post(route('order.confirm', $order->id));

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals(2, $order->status);
    }

    /** @test */
    public function test_vendor_can_reject_order()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1,
        ]);

        $this->actingAs($this->vendor);

        $response = $this->post(route('order.reject', $order->id), [
            'reason' => 'Out of stock',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals(5, $order->status); // Cancelled
    }

    /** @test */
    public function test_order_rejection_restores_stock()
    {
        $initialStock = $this->item->qty;

        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 2,
        ]);

        // Create order items
        $order->items()->create([
            'item_id' => $this->item->id,
            'qty' => 5,
            'price' => 25.00,
        ]);

        // Decrement stock
        $this->item->decrement('qty', 5);

        $this->actingAs($this->vendor);

        $response = $this->post(route('order.reject', $order->id), [
            'reason' => 'Customer request',
        ]);

        $this->item->refresh();
        $this->assertEquals($initialStock, $this->item->qty);
    }

    /** @test */
    public function test_customer_can_cancel_pending_order()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1,
        ]);

        $this->actingAs($this->customer);

        $response = $this->post(route('order.cancel', $order->id));

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals(5, $order->status);
    }

    /** @test */
    public function test_customer_cannot_cancel_confirmed_order()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 2, // Confirmed
        ]);

        $this->actingAs($this->customer);

        $response = $this->post(route('order.cancel', $order->id));

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Cannot cancel confirmed order']);
    }

    /** @test */
    public function test_order_sends_notifications_on_status_change()
    {
        Notification::fake();

        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1,
        ]);

        $this->actingAs($this->vendor);
        $this->post(route('order.confirm', $order->id));

        Notification::assertSentTo(
            $this->customer,
            \App\Notifications\OrderConfirmedNotification::class
        );
    }

    /** @test */
    public function test_order_calculates_totals_correctly()
    {
        $this->actingAs($this->customer);

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
            'qty' => 2,
            'price' => 25.00,
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
            'delivery_charge' => 5.00,
            'tax' => 4.50, // 9% tax on $50
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();

        $this->assertEquals(50.00, $order->sub_total);
        $this->assertEquals(5.00, $order->delivery_charge);
        $this->assertEquals(4.50, $order->tax);
        $this->assertEquals(59.50, $order->grand_total);
    }

    /** @test */
    public function test_order_applies_promocode_discount()
    {
        $this->actingAs($this->customer);

        $promocode = \App\Models\Promocode::factory()->create([
            'vendor_id' => $this->vendor->id,
            'code' => 'SAVE10',
            'discount' => 10.00,
            'discount_type' => 1, // Fixed
            'is_available' => 1,
        ]);

        Cart::factory()->create([
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'vendor_id' => $this->vendor->id,
            'qty' => 2,
            'price' => 25.00,
        ]);

        $address = Address::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $this->post(route('order.place'), [
            'address_id' => $address->id,
            'payment_type' => '1',
            'delivery_type' => '1',
            'promocode' => 'SAVE10',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();

        $this->assertEquals(50.00, $order->sub_total);
        $this->assertEquals(10.00, $order->discount_amount);
        $this->assertEquals(40.00, $order->grand_total);
    }

    /** @test */
    public function test_order_tracks_delivery_time()
    {
        $order = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'status' => 1,
            'created_at' => now()->subHours(2),
        ]);

        $this->actingAs($this->vendor);

        // Confirm order
        $this->post(route('order.confirm', $order->id));
        $order->refresh();
        $this->assertNotNull($order->accepted_at);

        // Mark as preparing
        $this->post(route('order.preparing', $order->id));
        $order->refresh();
        $this->assertNotNull($order->preparing_at);

        // Mark as ready
        $this->post(route('order.ready', $order->id));
        $order->refresh();
        $this->assertNotNull($order->ready_at);

        // Mark as delivered
        $this->post(route('order.delivered', $order->id));
        $order->refresh();
        $this->assertNotNull($order->delivered_at);
    }

    /** @test */
    public function test_customer_can_view_order_history()
    {
        Order::factory(5)->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
        ]);

        $this->actingAs($this->customer);

        $response = $this->get(route('orders.history'));

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    /** @test */
    public function test_vendor_can_view_order_list()
    {
        Order::factory(10)->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $this->actingAs($this->vendor);

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(10);
    }

    /** @test */
    public function test_order_generates_unique_order_number()
    {
        $order1 = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
        ]);

        $order2 = Order::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
        ]);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }
}
