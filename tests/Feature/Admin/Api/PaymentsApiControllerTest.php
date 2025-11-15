<?php

namespace Tests\Feature\Admin\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Payment;
use Laravel\Sanctum\Sanctum;

class PaymentsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $otherVendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::factory()->create(['type' => 2]);
        $this->otherVendor = User::factory()->create(['type' => 2]);
    }

    /** @test */
    public function can_list_payment_methods()
    {
        Sanctum::actingAs($this->vendor);

        Payment::factory()->count(3)->create(['vendor_id' => $this->vendor->id]);
        Payment::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/payments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_payments_by_availability()
    {
        Sanctum::actingAs($this->vendor);

        Payment::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'is_available' => 1
        ]);
        Payment::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_available' => 0
        ]);

        $response = $this->getJson('/api/admin/payments?is_available=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_show_payment_method()
    {
        Sanctum::actingAs($this->vendor);

        $payment = Payment::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->getJson('/api/admin/payments/' . $payment->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $payment->id);
    }

    /** @test */
    public function cannot_show_other_vendors_payment()
    {
        Sanctum::actingAs($this->vendor);

        $payment = Payment::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/payments/' . $payment->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_payment_method()
    {
        Sanctum::actingAs($this->vendor);

        $payment = Payment::factory()->create(['vendor_id' => $this->vendor->id]);

        $updateData = [
            'is_available' => 0,
            'public_key' => 'new_key_123',
        ];

        $response = $this->putJson('/api/admin/payments/' . $payment->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'is_available' => 0,
            'public_key' => 'new_key_123',
        ]);
    }

    /** @test */
    public function cannot_update_other_vendors_payment()
    {
        Sanctum::actingAs($this->vendor);

        $payment = Payment::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $updateData = ['is_available' => 0];

        $response = $this->putJson('/api/admin/payments/' . $payment->id, $updateData);

        $response->assertStatus(404);
    }
}
