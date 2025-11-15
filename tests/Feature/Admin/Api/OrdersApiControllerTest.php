<?php

namespace Tests\Feature\Admin\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\CustomStatus;
use App\Models\OrderDetails;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class OrdersApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $employee;
    protected Order $order;
    protected CustomStatus $customStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un vendor (type 2)
        $this->vendor = User::factory()->create([
            'type' => 2,
            'is_available' => 1,
        ]);

        // Créer un employé (type 4)
        $this->employee = User::factory()->create([
            'type' => 4,
            'vendor_id' => $this->vendor->id,
            'is_available' => 1,
        ]);

        // Créer une commande avec les colonnes minimales requises
        $this->order = Order::create([
            'vendor_id' => $this->vendor->id,
            'order_number' => 'TEST-' . time(),
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'mobile' => '1234567890',
            'address' => '123 Test St',
            'status' => 1,
            'status_type' => 1,
            'payment_type' => 1,
            'payment_status' => 1,
            'order_type' => 1,
            'sub_total' => 100.00,
            'tax' => 10.00,
            'grand_total' => 110.00,
        ]);

        // Créer des statuts personnalisés
        $this->customStatus = CustomStatus::factory()->create([
            'vendor_id' => $this->vendor->id,
            'type' => 2, // Accepted
            'order_type' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
            'name' => 'Order Accepted',
        ]);
    }

    /**
     * Helper: Get the admin API URL
     */
    protected function adminApiUrl(string $path): string
    {
        return "/admin{$path}";
    }

    /** @test */
    public function vendor_can_update_order_status()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/status", [
                'status_type' => 2, // Accepted
                'status_id' => $this->customStatus->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'order_number',
                    'customer',
                    'status',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Order status updated successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => $this->customStatus->id,
            'status_type' => 2,
        ]);
    }

    /** @test */
    public function employee_can_update_order_status()
    {
        $response = $this->actingAs($this->employee)
            ->patchJson("/admin/orders/{$this->order->id}/status", [
                'status_type' => 2,
                'status_id' => $this->customStatus->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function cannot_update_order_status_with_invalid_status()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/status", [
                'status_type' => 2,
                'status_id' => 99999, // Non-existent status
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid status provided',
            ]);
    }

    /** @test */
    public function cannot_update_other_vendor_order()
    {
        $otherVendor = User::factory()->create(['type' => 2]);
        $otherOrder = Order::create([
            'vendor_id' => $otherVendor->id,
            'order_number' => 'OTHER-' . time(),
            'customer_name' => 'Other Customer',
            'status' => 1,
            'sub_total' => 50.00,
            'grand_total' => 50.00,
        ]);

        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$otherOrder->id}/status", [
                'status_type' => 2,
                'status_id' => $this->customStatus->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access to this order',
            ]);
    }

    /** @test */
    public function updating_status_to_cancelled_restores_stock()
    {
        // Créer un produit avec stock
        $item = Item::create([
            'vendor_id' => $this->vendor->id,
            'item_name' => 'Test Product',
            'category_id' => 1,
            'price' => 10.00,
            'qty' => 10,
        ]);

        // Créer les détails de commande
        OrderDetails::factory()->create([
            'order_id' => $this->order->id,
            'item_id' => $item->id,
            'qty' => 3,
            'variants_id' => null,
        ]);

        // Créer statut "Cancelled"
        $cancelledStatus = CustomStatus::factory()->create([
            'vendor_id' => $this->vendor->id,
            'type' => 4, // Cancelled
            'order_type' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/status", [
                'status_type' => 4, // Cancelled
                'status_id' => $cancelledStatus->id,
            ]);

        $response->assertStatus(200);

        // Vérifier que le stock est restauré
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'qty' => 13, // 10 + 3
        ]);
    }

    /** @test */
    public function vendor_can_update_customer_info()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/customer-info", [
                'edit_type' => 'customer_info',
                'customer_name' => 'Jane Smith',
                'customer_mobile' => '9876543210',
                'customer_email' => 'jane@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer information updated successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'user_name' => 'Jane Smith',
            'user_mobile' => '9876543210',
            'user_email' => 'jane@example.com',
        ]);
    }

    /** @test */
    public function vendor_can_update_delivery_info()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/customer-info", [
                'edit_type' => 'delivery_info',
                'customer_address' => '123 Main St',
                'customer_building' => 'Building A',
                'customer_landmark' => 'Near Park',
                'customer_pincode' => '12345',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'address' => '123 Main St',
            'building' => 'Building A',
            'landmark' => 'Near Park',
            'pincode' => '12345',
        ]);
    }

    /** @test */
    public function customer_info_update_requires_valid_edit_type()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/customer-info", [
                'edit_type' => 'invalid_type',
                'customer_name' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['edit_type']);
    }

    /** @test */
    public function vendor_can_update_vendor_note()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/vendor-note", [
                'vendor_note' => 'Customer requested extra napkins',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vendor note updated successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'vendor_note' => 'Customer requested extra napkins',
        ]);
    }

    /** @test */
    public function vendor_note_is_required()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/vendor-note", [
                'vendor_note' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_note']);
    }

    /** @test */
    public function vendor_note_cannot_exceed_1000_characters()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/vendor-note", [
                'vendor_note' => str_repeat('a', 1001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_note']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_endpoints()
    {
        $response = $this->patchJson("/admin/orders/{$this->order->id}/status", [
            'status_type' => 2,
            'status_id' => $this->customStatus->id,
        ]);

        $response->assertStatus(401);

        $response = $this->patchJson("/admin/orders/{$this->order->id}/customer-info", [
            'edit_type' => 'customer_info',
            'customer_name' => 'Test',
        ]);

        $response->assertStatus(401);

        $response = $this->patchJson("/admin/orders/{$this->order->id}/vendor-note", [
            'vendor_note' => 'Test note',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function status_validation_rejects_invalid_status_type()
    {
        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$this->order->id}/status", [
                'status_type' => 99, // Invalid type
                'status_id' => $this->customStatus->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_type']);
    }

    /** @test */
    public function cash_on_delivery_payment_status_updated_on_delivery()
    {
        // Créer une commande COD (payment_type 6)
        $codOrder = Order::create([
            'vendor_id' => $this->vendor->id,
            'order_number' => 'COD-' . time(),
            'customer_name' => 'COD Customer',
            'payment_type' => 6, // COD
            'payment_status' => 1, // Unpaid
            'status' => 1,
            'sub_total' => 100.00,
            'grand_total' => 100.00,
        ]);

        // Créer statut "Delivered"
        $deliveredStatus = CustomStatus::factory()->create([
            'vendor_id' => $this->vendor->id,
            'type' => 3, // Delivered
            'order_type' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $response = $this->actingAs($this->vendor)
            ->patchJson("/admin/orders/{$codOrder->id}/status", [
                'status_type' => 3, // Delivered
                'status_id' => $deliveredStatus->id,
            ]);

        $response->assertStatus(200);

        // Vérifier que le payment_status passe à 2 (Paid)
        $this->assertDatabaseHas('orders', [
            'id' => $codOrder->id,
            'payment_status' => 2,
        ]);
    }
}
