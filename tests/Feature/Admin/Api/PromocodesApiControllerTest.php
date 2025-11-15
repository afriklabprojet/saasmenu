<?php

namespace Tests\Feature\Admin\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Promocode;
use Laravel\Sanctum\Sanctum;

class PromocodesApiControllerTest extends TestCase
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
    public function can_list_promocodes()
    {
        Sanctum::actingAs($this->vendor);

        Promocode::factory()->count(3)->create(['vendor_id' => $this->vendor->id]);
        Promocode::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/promocodes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_active_promocodes()
    {
        Sanctum::actingAs($this->vendor);

        Promocode::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'start_date' => now()->subDays(5),
            'exp_date' => now()->addDays(5),
        ]);

        Promocode::factory()->create([
            'vendor_id' => $this->vendor->id,
            'start_date' => now()->subDays(10),
            'exp_date' => now()->subDays(5),
        ]);

        $response = $this->getJson('/api/admin/promocodes?is_active=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_create_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $data = [
            'offer_name' => 'Summer Sale',
            'offer_code' => 'SUMMER2025',
            'offer_amount' => 20,
            'offer_type' => 1,
            'min_amount' => 50,
            'usage_type' => 1,
            'usage_limit' => 100,
            'start_date' => now()->format('Y-m-d'),
            'exp_date' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/admin/promocodes', $data);

        $response->assertStatus(201)
            ->assertJsonPath('promocode.offer_code', 'SUMMER2025');

        $this->assertDatabaseHas('promocodes', [
            'vendor_id' => $this->vendor->id,
            'offer_code' => 'SUMMER2025',
        ]);
    }

    /** @test */
    public function offer_code_must_be_unique_per_vendor()
    {
        Sanctum::actingAs($this->vendor);

        Promocode::factory()->create([
            'vendor_id' => $this->vendor->id,
            'offer_code' => 'UNIQUE123',
        ]);

        $data = [
            'offer_name' => 'New Offer',
            'offer_code' => 'UNIQUE123',
            'offer_amount' => 10,
            'offer_type' => 1,
            'min_amount' => 0,
            'usage_type' => 1,
            'usage_limit' => 10,
            'start_date' => now()->format('Y-m-d'),
            'exp_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/admin/promocodes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['offer_code']);
    }

    /** @test */
    public function exp_date_must_be_after_start_date()
    {
        Sanctum::actingAs($this->vendor);

        $data = [
            'offer_name' => 'Invalid Dates',
            'offer_code' => 'INVALID',
            'offer_amount' => 10,
            'offer_type' => 1,
            'min_amount' => 0,
            'usage_type' => 1,
            'usage_limit' => 10,
            'start_date' => now()->format('Y-m-d'),
            'exp_date' => now()->subDays(5)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/admin/promocodes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['exp_date']);
    }

    /** @test */
    public function can_show_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $promocode = Promocode::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->getJson('/api/admin/promocodes/' . $promocode->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $promocode->id);
    }

    /** @test */
    public function cannot_show_other_vendors_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $promocode = Promocode::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->getJson('/api/admin/promocodes/' . $promocode->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $promocode = Promocode::factory()->create(['vendor_id' => $this->vendor->id]);

        $updateData = [
            'offer_name' => 'Updated Offer',
            'offer_amount' => 30,
            'is_active' => 0,
        ];

        $response = $this->putJson('/api/admin/promocodes/' . $promocode->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('promocode.offer_name', 'Updated Offer');

        $this->assertDatabaseHas('promocodes', [
            'id' => $promocode->id,
            'offer_name' => 'Updated Offer',
            'offer_amount' => 30,
        ]);
    }

    /** @test */
    public function can_delete_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $promocode = Promocode::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->deleteJson('/api/admin/promocodes/' . $promocode->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('promocodes', ['id' => $promocode->id]);
    }

    /** @test */
    public function cannot_delete_other_vendors_promocode()
    {
        Sanctum::actingAs($this->vendor);

        $promocode = Promocode::factory()->create(['vendor_id' => $this->otherVendor->id]);

        $response = $this->deleteJson('/api/admin/promocodes/' . $promocode->id);

        $response->assertStatus(404);

        $this->assertDatabaseHas('promocodes', ['id' => $promocode->id]);
    }
}
