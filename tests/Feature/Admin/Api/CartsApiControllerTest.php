<?php

namespace Tests\Feature\Admin\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cart;
use App\Models\Item;
use Laravel\Sanctum\Sanctum;

class CartsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $otherVendor;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::factory()->create(['type' => 2]);
        $this->otherVendor = User::factory()->create(['type' => 2]);
        $this->customer = User::factory()->create(['type' => 3]);
    }

    /** @test */
    public function can_list_all_carts_for_vendor()
    {
        Sanctum::actingAs($this->vendor);

        $item = Item::factory()->create(['vendor_id' => $this->vendor->id]);

        Cart::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'product_id' => $item->id,
        ]);

        Cart::factory()->create([
            'vendor_id' => $this->otherVendor->id,
            'user_id' => $this->customer->id,
            'product_id' => $item->id,
        ]);

        $response = $this->getJson('/api/admin/carts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_carts_by_user_id()
    {
        Sanctum::actingAs($this->vendor);

        $customer2 = User::factory()->create(['type' => 3]);
        $item = Item::factory()->create(['vendor_id' => $this->vendor->id]);

        Cart::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'product_id' => $item->id,
        ]);

        Cart::factory()->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $customer2->id,
            'product_id' => $item->id,
        ]);

        $response = $this->getJson('/api/admin/carts?user_id=' . $this->customer->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_carts_by_session_id()
    {
        Sanctum::actingAs($this->vendor);

        $item = Item::factory()->create(['vendor_id' => $this->vendor->id]);
        $sessionId = 'session_123';

        Cart::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'session_id' => $sessionId,
            'user_id' => 0,
            'product_id' => $item->id,
        ]);

        Cart::factory()->create([
            'vendor_id' => $this->vendor->id,
            'session_id' => 'session_456',
            'user_id' => 0,
            'product_id' => $item->id,
        ]);

        $response = $this->getJson('/api/admin/carts?session_id=' . $sessionId);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_delete_cart_item()
    {
        Sanctum::actingAs($this->vendor);

        $item = Item::factory()->create(['vendor_id' => $this->vendor->id]);

        $cart = Cart::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'session_id' => null,
            'product_id' => $item->id,
            'product_name' => $item->name,
            'product_slug' => $item->slug,
            'product_image' => $item->image,
            'qty' => 2,
            'product_price' => 10.00,
            'product_tax' => 1.00,
        ]);

        $response = $this->deleteJson('/api/admin/carts/' . $cart->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cart item deleted successfully']);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    /** @test */
    public function cannot_delete_other_vendors_cart_item()
    {
        Sanctum::actingAs($this->vendor);

        $item = Item::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $cart = Cart::create([
            'vendor_id' => $this->otherVendor->id,
            'user_id' => $this->customer->id,
            'session_id' => null,
            'product_id' => $item->id,
            'product_name' => $item->name,
            'product_slug' => $item->slug,
            'product_image' => $item->image,
            'qty' => 2,
            'product_price' => 10.00,
            'product_tax' => 1.00,
        ]);

        $response = $this->deleteJson('/api/admin/carts/' . $cart->id);

        $response->assertStatus(404);

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
    }

    /** @test */
    public function returns_404_when_deleting_non_existent_cart()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->deleteJson('/api/admin/carts/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function cart_items_are_paginated()
    {
        Sanctum::actingAs($this->vendor);

        $item = Item::factory()->create(['vendor_id' => $this->vendor->id]);

        Cart::factory()->count(20)->create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->customer->id,
            'product_id' => $item->id,
        ]);

        $response = $this->getJson('/api/admin/carts?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('total', 20)
            ->assertJsonPath('per_page', 10);
    }
}
