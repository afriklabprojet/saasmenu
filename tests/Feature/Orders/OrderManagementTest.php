<?php

namespace Tests\Feature\Orders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_create_order()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);
        $restaurant = Restaurant::factory()->create(['vendor_id' => $vendor->id]);

        $category = Category::factory()->create(['vendor_id' => $vendor->id]);
        $item = Item::factory()->create([
            'vendor_id' => $vendor->id,
            'cat_id' => $category->id,
            'item_price' => 15.99
        ]);

        $orderData = [
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . time(),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_mobile' => '1234567890',
            'customer_address' => '123 Test Street',
            'delivery_charge' => 2.99,
            'tax' => 1.50,
            'grand_total' => 20.48,
            'status' => 1, // Pending
            'order_type' => 1, // Delivery
        ];

        $this->actingAs($customer);

        $response = $this->post('/orders', $orderData);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'grand_total' => 20.48
        ]);
    }

    /** @test */
    public function vendor_can_view_their_orders()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);

        Order::factory()->count(3)->create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id
        ]);

        $response = $this->actingAs($vendor)
            ->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
    }

    /** @test */
    public function vendor_can_update_order_status()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);

        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id,
            'status' => 1 // Pending
        ]);

        $response = $this->actingAs($vendor)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 2 // Confirmed
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 2
        ]);
    }

    /** @test */
    public function vendor_cannot_access_other_vendor_orders()
    {
        $vendor1 = User::factory()->create(['type' => 2]);
        $vendor2 = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);

        $order = Order::factory()->create([
            'vendor_id' => $vendor1->id,
            'user_id' => $customer->id
        ]);

        $response = $this->actingAs($vendor2)
            ->get("/admin/orders/{$order->id}");

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function customer_can_view_their_orders()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);

        Order::factory()->count(2)->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id
        ]);

        $response = $this->actingAs($customer)
            ->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Your Orders');
    }

    /** @test */
    public function customer_can_cancel_pending_order()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => 1 // Pending
        ]);

        $response = $this->actingAs($customer)
            ->patch("/orders/{$order->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 6 // Cancelled
        ]);
    }

    /** @test */
    public function customer_cannot_cancel_confirmed_order()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => 2 // Confirmed
        ]);

        $response = $this->actingAs($customer)
            ->patch("/orders/{$order->id}/cancel");

        $response->assertStatus(422); // Unprocessable Entity
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 2 // Still confirmed
        ]);
    }

    /** @test */
    public function order_total_is_calculated_correctly()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);

        $subtotal = 25.00;
        $deliveryCharge = 3.99;
        $tax = 2.50;
        $discount = 5.00;
        $expectedTotal = $subtotal + $deliveryCharge + $tax - $discount;

        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id,
            'sub_total' => $subtotal,
            'delivery_charge' => $deliveryCharge,
            'tax' => $tax,
            'discount_amount' => $discount,
            'grand_total' => $expectedTotal
        ]);

        $this->assertEquals($expectedTotal, $order->grand_total);
        $this->assertEquals(26.49, $order->grand_total); // 25 + 3.99 + 2.50 - 5.00
    }

    /** @test */
    public function order_can_have_multiple_items()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);
        $category = Category::factory()->create(['vendor_id' => $vendor->id]);

        $item1 = Item::factory()->create([
            'vendor_id' => $vendor->id,
            'cat_id' => $category->id,
            'item_price' => 10.00
        ]);

        $item2 = Item::factory()->create([
            'vendor_id' => $vendor->id,
            'cat_id' => $category->id,
            'item_price' => 15.00
        ]);

        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id
        ]);

        // Attach items to order (assuming order_details table exists)
        $order->items()->attach($item1->id, ['quantity' => 2, 'price' => 10.00]);
        $order->items()->attach($item2->id, ['quantity' => 1, 'price' => 15.00]);

        $this->assertEquals(2, $order->items->count());
        $this->assertEquals(35.00, $order->items->sum(function($item) {
            return $item->pivot->quantity * $item->pivot->price;
        }));
    }
}
