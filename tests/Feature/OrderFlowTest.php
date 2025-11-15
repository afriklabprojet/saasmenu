<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Settings;
use App\Models\Item;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Payment;
use App\Models\DeliveryArea;
use App\Models\Coupons;
use App\Models\Timing;
use App\Models\Variants;
use Illuminate\Support\Facades\Session;

/**
 * Tests Feature pour le flux complet de commande
 * Checkout → Payment → Success → Track → Cancel
 */
class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $customer;
    protected $item;
    protected $deliveryArea;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un vendor de test
        $this->vendor = User::factory()->create([
            'type' => 2, // vendor
            'is_available' => 1,
            'is_verified' => 1,
            'login_type' => 'email',
        ]);

        // Créer settings pour le vendor
        Settings::create([
            'vendor_id' => $this->vendor->id,
            'timezone' => 'UTC',
            'currency' => 'USD',
            'currency_position' => 'left',
            'min_order' => 10.00,
            'default_tax' => 10,
            'delivery_charge' => 5.00,
        ]);

        // Créer un customer
        $this->customer = User::factory()->create([
            'type' => 3, // customer
            'is_available' => 1,
            'login_type' => 'email',
        ]);

        // Créer une catégorie
        $category = Category::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Category',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer un item
        $this->item = Item::create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'name' => 'Test Item',
            'price' => 25.00,
            'tax' => json_encode([['name' => 'VAT', 'type' => '2', 'tax' => '10']]),
            'min_order' => 1,
            'max_order' => 10,
            'stock_qty' => 100,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer une zone de livraison
        $this->deliveryArea = DeliveryArea::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Zone A',
            'price' => 5.00,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer timing
        Timing::create([
            'vendor_id' => $this->vendor->id,
            'day' => date('l'),
            'break_start' => '12:00',
            'break_end' => '13:00',
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_available' => 1,
        ]);
    }

    /** @test */
    public function test_checkout_page_loads_successfully()
    {
        // Créer un item dans le panier
        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'qty' => 2,
            'price' => $this->item->price,
            'tax' => $this->item->tax,
        ]);

        // Simuler la session
        Session::put('restaurant_id', $this->vendor->id);

        $response = $this->actingAs($this->customer)
            ->get(route('checkout', ['slug' => $this->vendor->slug]));

        $response->assertStatus(200);
        $response->assertViewHas('cartdata');
        $response->assertViewHas('deliveryareas');
    }

    /** @test */
    public function test_checkout_validates_cart_stock()
    {
        // Créer un item avec stock insuffisant
        $this->item->update(['stock_qty' => 1]);

        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'qty' => 5, // Plus que le stock disponible
            'price' => $this->item->price,
            'tax' => $this->item->tax,
        ]);

        Session::put('restaurant_id', $this->vendor->id);

        $response = $this->actingAs($this->customer)
            ->get(route('checkout', ['slug' => $this->vendor->slug]));

        // Devrait rediriger avec erreur de stock
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_apply_promocode_success()
    {
        // Créer un coupon valide
        $coupon = Coupons::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'TEST10',
            'type' => 'percentage',
            'discount' => 10,
            'min_amount' => 20,
            'max_discount_amount' => 100,
            'limit' => 100,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        Session::put('restaurant_id', $this->vendor->id);

        $response = $this->actingAs($this->customer)
            ->post(route('applypromocode', ['slug' => $this->vendor->slug]), [
                'coupon_code' => 'TEST10',
                'sub_total' => 50.00,
            ]);

        $response->assertJson(['status' => true]);
        $this->assertTrue(Session::has('coupon_code'));
        $this->assertEquals('TEST10', Session::get('coupon_code'));
    }

    /** @test */
    public function test_apply_promocode_expired()
    {
        // Créer un coupon expiré
        $coupon = Coupons::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'discount' => 10,
            'min_amount' => 20,
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'end_date' => now()->subDay()->format('Y-m-d'),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        Session::put('restaurant_id', $this->vendor->id);

        $response = $this->actingAs($this->customer)
            ->post(route('applypromocode', ['slug' => $this->vendor->slug]), [
                'coupon_code' => 'EXPIRED',
                'sub_total' => 50.00,
            ]);

        $response->assertJson(['status' => false]);
    }

    /** @test */
    public function test_timeslot_generation()
    {
        Session::put('restaurant_id', $this->vendor->id);

        $response = $this->actingAs($this->customer)
            ->post(route('timeslot', ['slug' => $this->vendor->slug]), [
                'date' => now()->addDay()->format('Y-m-d'),
            ]);

        $response->assertJson(['status' => true]);
        $response->assertJsonStructure([
            'status',
            'timeslot' => [
                '*' => ['time', 'display']
            ]
        ]);
    }

    /** @test */
    public function test_payment_method_cod_creates_order()
    {
        // Créer items dans le panier
        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'qty' => 2,
            'price' => $this->item->price,
            'tax' => $this->item->tax,
        ]);

        Session::put('restaurant_id', $this->vendor->id);
        Session::put('delivery_type', '1'); // Delivery
        Session::put('address', '123 Test Street');
        Session::put('delivery_area', $this->deliveryArea->id);

        $response = $this->actingAs($this->customer)
            ->post(route('paymentmethod', ['slug' => $this->vendor->slug]), [
                'payment_type' => '3', // COD
                'notes' => 'Test order',
            ]);

        // Vérifier que la commande a été créée
        $this->assertDatabaseHas('orders', [
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function test_order_success_page()
    {
        // Créer une commande de test
        $order = Order::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'order_number' => 'TEST-' . time(),
            'order_type' => 1,
            'status' => 1,
            'payment_type' => '3',
            'payment_id' => null,
            'grand_total' => 50.00,
            'tax' => 5.00,
            'delivery_charge' => 5.00,
            'grand_total' => 60.00,
            'address' => '123 Test Street',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'item_id' => $this->item->id,
            'name' => $this->item->name,
            'price' => $this->item->price,
            'qty' => 2,
            'tax' => $this->item->tax,
            'price' => $this->item->price * 2,
        ]);

        Payment::create([
            'vendor_id' => $this->vendor->id,
            'order_number' => $order->order_number,
            'amount' => 60.00,
            'payment_type' => '3',
            'status' => 2, // Success
        ]);

        Session::put('order_number', $order->order_number);

        $response = $this->actingAs($this->customer)
            ->get(route('success', ['slug' => $this->vendor->slug]));

        $response->assertStatus(200);
        $response->assertViewHas('orderdata');
    }

    /** @test */
    public function test_track_order()
    {
        // Créer une commande
        $order = Order::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'order_number' => 'TRACK-' . time(),
            'order_type' => 1,
            'status' => 2, // Processing
            'payment_type' => '3',
            'grand_total' => 50.00,
            'grand_total' => 60.00,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('track', [
                'slug' => $this->vendor->slug,
                'order_number' => $order->order_number,
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('orderinfo');
    }

    /** @test */
    public function test_cancel_order()
    {
        // Créer une commande
        $order = Order::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'order_number' => 'CANCEL-' . time(),
            'order_type' => 1,
            'status' => 1, // Pending
            'payment_type' => '3',
            'grand_total' => 50.00,
            'grand_total' => 60.00,
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'item_id' => $this->item->id,
            'name' => $this->item->name,
            'price' => $this->item->price,
            'qty' => 2,
            'tax' => $this->item->tax,
            'price' => $this->item->price * 2,
        ]);

        // Stock initial
        $initialStock = $this->item->stock_qty;

        $response = $this->actingAs($this->customer)
            ->post(route('cancel', ['slug' => $this->vendor->slug]), [
                'order_number' => $order->order_number,
                'cancel_reason' => 'Test cancellation',
            ]);

        // Vérifier que le statut a changé
        $order->refresh();
        $this->assertNotEquals(1, $order->status);

        // Vérifier que le stock a été restauré
        $this->item->refresh();
        $this->assertEquals($initialStock + 2, $this->item->stock_qty);

        $response->assertRedirect();
    }

    /** @test */
    public function test_complete_order_flow()
    {
        // 1. Ajouter au panier
        Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'item_id' => $this->item->id,
            'qty' => 2,
            'price' => $this->item->price,
            'tax' => $this->item->tax,
        ]);

        Session::put('restaurant_id', $this->vendor->id);

        // 2. Checkout
        $checkoutResponse = $this->actingAs($this->customer)
            ->get(route('checkout', ['slug' => $this->vendor->slug]));
        $checkoutResponse->assertStatus(200);

        // 3. Appliquer coupon
        $coupon = Coupons::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'FLOW10',
            'type' => 'percentage',
            'discount' => 10,
            'min_amount' => 20,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $couponResponse = $this->actingAs($this->customer)
            ->post(route('applypromocode', ['slug' => $this->vendor->slug]), [
                'coupon_code' => 'FLOW10',
                'sub_total' => 50.00,
            ]);
        $couponResponse->assertJson(['status' => true]);

        // 4. Payment COD
        Session::put('delivery_type', '1');
        Session::put('address', '123 Test Street');
        Session::put('delivery_area', $this->deliveryArea->id);

        $paymentResponse = $this->actingAs($this->customer)
            ->post(route('paymentmethod', ['slug' => $this->vendor->slug]), [
                'payment_type' => '3',
                'notes' => 'Complete flow test',
            ]);

        // 5. Vérifier création commande
        $this->assertDatabaseHas('orders', [
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'payment_type' => '3',
        ]);

        $order = Order::where('vendor_id', $this->vendor->id)
            ->where('user_id', $this->customer->id)
            ->latest()
            ->first();

        // 6. Page success
        Session::put('order_number', $order->order_number);
        $successResponse = $this->actingAs($this->customer)
            ->get(route('success', ['slug' => $this->vendor->slug]));
        $successResponse->assertStatus(200);

        // 7. Track
        $trackResponse = $this->actingAs($this->customer)
            ->get(route('track', [
                'slug' => $this->vendor->slug,
                'order_number' => $order->order_number,
            ]));
        $trackResponse->assertStatus(200);
    }
}
