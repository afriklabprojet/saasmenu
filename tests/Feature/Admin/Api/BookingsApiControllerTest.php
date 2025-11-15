<?php

namespace Tests\Feature\Admin\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use Laravel\Sanctum\Sanctum;

class BookingsApiControllerTest extends TestCase
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
    public function can_list_bookings()
    {
        Sanctum::actingAs($this->vendor);

        Booking::factory()->count(3)->create(['vendor_id' => $this->vendor->id]);
        Booking::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_bookings_by_payment_status()
    {
        Sanctum::actingAs($this->vendor);

        Booking::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'payment_status' => 1,
        ]);

        Booking::factory()->create([
            'vendor_id' => $this->vendor->id,
            'payment_status' => 0,
        ]);

        $response = $this->getJson('/api/admin/bookings?payment_status=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_show_booking()
    {
        Sanctum::actingAs($this->vendor);

        $booking = Booking::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->getJson('/api/admin/bookings/' . $booking->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $booking->id);
    }

    /** @test */
    public function cannot_show_other_vendors_booking()
    {
        Sanctum::actingAs($this->vendor);

        $booking = Booking::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/bookings/' . $booking->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_booking_payment_status()
    {
        Sanctum::actingAs($this->vendor);

        $booking = Booking::factory()->create([
            'vendor_id' => $this->vendor->id,
            'payment_status' => 0,
        ]);

        $updateData = ['payment_status' => 1];

        $response = $this->putJson('/api/admin/bookings/' . $booking->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 1,
        ]);
    }

    /** @test */
    public function can_delete_booking()
    {
        Sanctum::actingAs($this->vendor);

        $booking = Booking::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->deleteJson('/api/admin/bookings/' . $booking->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    /** @test */
    public function cannot_delete_other_vendors_booking()
    {
        Sanctum::actingAs($this->vendor);

        $booking = Booking::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->deleteJson('/api/admin/bookings/' . $booking->id);

        $response->assertStatus(404);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }
}
