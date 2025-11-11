<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderStatusChanged;

/**
 * Tests du workflow complet des commandes
 * 
 * Couvre le cycle de vie complet d'une commande:
 * 1. Création de commande
 * 2. Mise à jour du statut (Pending → Confirmed → Preparing → Ready → Delivered)
 * 3. Annulation de commande
 * 4. Remboursement
 * 5. Notifications
 */
class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $customer;
    protected User $vendorUser;
    protected Vendor $vendor;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client
        $this->customer = User::factory()->create([
            'email' => 'customer@example.com',
            'type' => 3, // Customer
            'name' => 'Test Customer',
            'mobile' => '1234567890',
        ]);

        // Créer un utilisateur vendor
        $this->vendorUser = User::factory()->create([
            'email' => 'vendor@example.com',
            'type' => 2, // Vendor
            'name' => 'Test Vendor',
        ]);

        // Créer un vendor
        $this->vendor = Vendor::factory()->create([
            'vendor_id' => $this->vendorUser->id,
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'is_available' => 1,
        ]);

        // Créer un item
        $this->item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'item_name' => 'Test Pizza',
            'item_price' => 1500, // 15.00
            'item_original_price' => 1500,
            'is_available' => 1,
            'stock_qty' => 100,
        ]);

        // Set vendor session
        Session::put('vendor_id', $this->vendor->id);
        Session::put('slug', $this->vendor->slug);
    }

    /**
     * ========================================
     * 1. Tests de Création de Commande
     * ========================================
     */

    /** @test */
    public function test_customer_can_create_order_from_cart()
    {
        $this->actingAs($this->customer);

        // Ajouter des items au panier
        Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'item_id' => $this->item->id,
            'qty' => 2,
            'price' => $this->item->item_price,
        ]);

        // Créer la commande
        $orderData = [
            'payment_type' => 'COD',
            'address' => '123 Test Street',
            'building' => 'Building A',
            'landmark' => 'Near Mall',
            'postal_code' => '12345',
            'delivery_charge' => 500,
            'notes' => 'Please ring bell twice',
        ];

        $response = $this->post(route('v2.ordercreate'), $orderData);

        $response->assertStatus(302); // Redirect

        // Vérifier la création de la commande
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'payment_type' => 'COD',
            'order_status' => 1, // Pending
        ]);

        // Vérifier les items de commande
        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $this->item->id,
            'qty' => 2,
        ]);

        // Vérifier que le panier est vidé
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function test_order_number_is_unique()
    {
        $this->actingAs($this->customer);

        // Créer deux commandes
        $order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    /** @test */
    public function test_order_stores_delivery_information()
    {
        $this->actingAs($this->customer);

        Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'item_id' => $this->item->id,
            'qty' => 1,
            'price' => $this->item->item_price,
        ]);

        $orderData = [
            'payment_type' => 'COD',
            'address' => '456 Main Street',
            'building' => 'Tower B, Floor 3',
            'landmark' => 'Opposite Park',
            'postal_code' => '67890',
            'delivery_charge' => 700,
            'notes' => 'Leave at door',
            'delivery_type' => 'delivery', // or 'pickup'
        ];

        $this->post(route('v2.ordercreate'), $orderData);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'address' => '456 Main Street',
            'building' => 'Tower B, Floor 3',
            'landmark' => 'Opposite Park',
            'postal_code' => '67890',
            'notes' => 'Leave at door',
        ]);
    }

    /** @test */
    public function test_order_cannot_be_created_with_empty_cart()
    {
        $this->actingAs($this->customer);

        $orderData = [
            'payment_type' => 'COD',
            'address' => '123 Test Street',
            'delivery_charge' => 500,
        ];

        $response = $this->post(route('v2.ordercreate'), $orderData);

        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->customer->id,
        ]);
    }

    /**
     * ========================================
     * 2. Tests de Mise à Jour de Statut
     * ========================================
     */

    /** @test */
    public function test_vendor_can_confirm_order()
    {
        $this->actingAs($this->vendorUser);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1, // Pending
        ]);

        $response = $this->post(route('admin.order.updatestatus'), [
            'order_id' => $order->id,
            'status' => 2, // Confirmed
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(2, $order->order_status);
    }

    /** @test */
    public function test_order_status_follows_correct_sequence()
    {
        $this->actingAs($this->vendorUser);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1, // Pending
        ]);

        // Pending (1) → Confirmed (2)
        $order->update(['order_status' => 2]);
        $this->assertEquals(2, $order->order_status);

        // Confirmed (2) → Preparing (3)
        $order->update(['order_status' => 3]);
        $this->assertEquals(3, $order->order_status);

        // Preparing (3) → Ready (4)
        $order->update(['order_status' => 4]);
        $this->assertEquals(4, $order->order_status);

        // Ready (4) → Out for Delivery (5)
        $order->update(['order_status' => 5]);
        $this->assertEquals(5, $order->order_status);

        // Out for Delivery (5) → Delivered (6)
        $order->update(['order_status' => 6]);
        $this->assertEquals(6, $order->order_status);
    }

    /** @test */
    public function test_customer_receives_notification_on_status_change()
    {
        Notification::fake();

        $this->actingAs($this->vendorUser);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1,
        ]);

        $this->post(route('admin.order.updatestatus'), [
            'order_id' => $order->id,
            'status' => 2,
        ]);

        // Vérifier que la notification est envoyée
        Notification::assertSentTo(
            [$this->customer],
            OrderStatusChanged::class
        );
    }

    /** @test */
    public function test_order_timestamps_are_updated()
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1,
        ]);

        $initialUpdatedAt = $order->updated_at;

        sleep(1);

        $order->update(['order_status' => 2]);
        $order->refresh();

        $this->assertNotEquals($initialUpdatedAt, $order->updated_at);
    }

    /**
     * ========================================
     * 3. Tests d'Annulation de Commande
     * ========================================
     */

    /** @test */
    public function test_customer_can_cancel_pending_order()
    {
        $this->actingAs($this->customer);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1, // Pending
        ]);

        $response = $this->post(route('v2.cancel'), [
            'order_id' => $order->id,
            'reason' => 'Changed my mind',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(7, $order->order_status); // Cancelled
        $this->assertEquals('Changed my mind', $order->cancel_reason);
    }

    /** @test */
    public function test_customer_cannot_cancel_delivered_order()
    {
        $this->actingAs($this->customer);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 6, // Delivered
        ]);

        $response = $this->post(route('v2.cancel'), [
            'order_id' => $order->id,
            'reason' => 'Too late',
        ]);

        $response->assertStatus(403); // Forbidden

        $order->refresh();
        $this->assertEquals(6, $order->order_status); // Still Delivered
    }

    /** @test */
    public function test_cancellation_reason_is_required()
    {
        $this->actingAs($this->customer);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1,
        ]);

        $response = $this->post(route('v2.cancel'), [
            'order_id' => $order->id,
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    /** @test */
    public function test_cancelled_order_restores_item_stock()
    {
        $this->actingAs($this->customer);

        $initialStock = $this->item->stock_qty;

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_status' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $this->item->id,
            'qty' => 5,
            'price' => $this->item->item_price,
        ]);

        // Stock devrait être réduit
        $this->item->refresh();
        $this->assertEquals($initialStock - 5, $this->item->stock_qty);

        // Annuler la commande
        $this->post(route('v2.cancel'), [
            'order_id' => $order->id,
            'reason' => 'Test cancellation',
        ]);

        // Stock devrait être restauré
        $this->item->refresh();
        $this->assertEquals($initialStock, $this->item->stock_qty);
    }

    /**
     * ========================================
     * 4. Tests de Tracking de Commande
     * ========================================
     */

    /** @test */
    public function test_customer_can_track_order()
    {
        $this->actingAs($this->customer);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'order_number' => 'ORDER123456',
            'order_status' => 3, // Preparing
        ]);

        $response = $this->get(route('v2.track', ['order_number' => 'ORDER123456']));

        $response->assertStatus(200);
        $response->assertSee('ORDER123456');
        $response->assertSee('Preparing');
    }

    /** @test */
    public function test_tracking_requires_valid_order_number()
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('v2.track', ['order_number' => 'INVALID']));

        $response->assertStatus(404);
    }

    /** @test */
    public function test_customer_can_only_track_own_orders()
    {
        $otherCustomer = User::factory()->create(['type' => 3]);

        $order = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'vendor_id' => $this->vendor->id,
            'order_number' => 'ORDER789',
        ]);

        $this->actingAs($this->customer);

        $response = $this->get(route('v2.track', ['order_number' => 'ORDER789']));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * ========================================
     * 5. Tests de Calculs de Commande
     * ========================================
     */

    /** @test */
    public function test_order_calculates_subtotal_correctly()
    {
        $this->actingAs($this->customer);

        Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'item_id' => $this->item->id,
            'qty' => 3,
            'price' => $this->item->item_price,
        ]);

        $this->post(route('v2.ordercreate'), [
            'payment_type' => 'COD',
            'address' => '123 Test Street',
            'delivery_charge' => 500,
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();

        $expectedSubtotal = $this->item->item_price * 3;
        $this->assertEquals($expectedSubtotal, $order->sub_total);
    }

    /** @test */
    public function test_order_applies_discount_correctly()
    {
        $this->actingAs($this->customer);

        Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'item_id' => $this->item->id,
            'qty' => 2,
            'price' => $this->item->item_price,
        ]);

        $discountAmount = 500; // 5.00 discount

        $this->post(route('v2.ordercreate'), [
            'payment_type' => 'COD',
            'address' => '123 Test Street',
            'delivery_charge' => 500,
            'discount' => $discountAmount,
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();

        $this->assertEquals($discountAmount, $order->discount_amount);
        
        $expectedTotal = ($this->item->item_price * 2) + 500 - $discountAmount;
        $this->assertEquals($expectedTotal, $order->grand_total);
    }

    /** @test */
    public function test_order_includes_tax_correctly()
    {
        $this->actingAs($this->customer);

        Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'item_id' => $this->item->id,
            'qty' => 1,
            'price' => $this->item->item_price,
            'tax' => 150, // 1.50 tax
        ]);

        $this->post(route('v2.ordercreate'), [
            'payment_type' => 'COD',
            'address' => '123 Test Street',
            'delivery_charge' => 500,
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();

        $this->assertGreaterThan(0, $order->tax);
    }
}
