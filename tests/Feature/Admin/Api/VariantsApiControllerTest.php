<?php

namespace Tests\Feature\Admin\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Variants;
use Laravel\Sanctum\Sanctum;

class VariantsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $otherVendor;
    protected Item $item;
    protected Item $otherItem;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendors
        $this->vendor = User::factory()->create(['type' => 2]);
        $this->otherVendor = User::factory()->create(['type' => 2]);

        // Create items
        $this->item = Item::factory()->create(['vendor_id' => $this->vendor->id]);
        $this->otherItem = Item::factory()->create(['vendor_id' => $this->otherVendor->id]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_variants()
    {
        $response = $this->getJson('/api/admin/variants');
        $response->assertStatus(401);
    }

    /** @test */
    public function can_list_variants()
    {
        Sanctum::actingAs($this->vendor);

        Variants::factory()->count(3)->create(['item_id' => $this->item->id]);
        Variants::factory()->create(['item_id' => $this->otherItem->id]);

        $response = $this->getJson('/api/admin/variants');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_filter_variants_by_item_id()
    {
        Sanctum::actingAs($this->vendor);

        $item2 = Item::factory()->create(['vendor_id' => $this->vendor->id]);

        Variants::factory()->count(2)->create(['item_id' => $this->item->id]);
        Variants::factory()->create(['item_id' => $item2->id]);

        $response = $this->getJson('/api/admin/variants?item_id=' . $this->item->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_variants_by_availability()
    {
        Sanctum::actingAs($this->vendor);

        Variants::factory()->count(2)->create([
            'item_id' => $this->item->id,
            'is_available' => 1
        ]);
        Variants::factory()->create([
            'item_id' => $this->item->id,
            'is_available' => 0
        ]);

        $response = $this->getJson('/api/admin/variants?is_available=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_create_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variantData = [
            'item_id' => $this->item->id,
            'name' => 'Large',
            'price' => 15.99,
            'original_price' => 20.00,
            'qty' => 50,
            'min_order' => 1,
            'max_order' => 10,
            'is_available' => 1,
            'stock_management' => 1,
        ];

        $response = $this->postJson('/api/admin/variants', $variantData);

        $response->assertStatus(201)
            ->assertJsonPath('variant.name', 'Large')
            ->assertJsonPath('variant.price', 15.99);

        $this->assertDatabaseHas('variants', [
            'item_id' => $this->item->id,
            'name' => 'Large',
            'price' => 15.99,
        ]);
    }

    /** @test */
    public function cannot_create_variant_for_other_vendors_item()
    {
        Sanctum::actingAs($this->vendor);

        $variantData = [
            'item_id' => $this->otherItem->id,
            'name' => 'Large',
            'price' => 15.99,
            'original_price' => 20.00,
            'qty' => 50,
        ];

        $response = $this->postJson('/api/admin/variants', $variantData);

        $response->assertStatus(422);
    }

    /** @test */
    public function name_is_required_when_creating_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variantData = [
            'item_id' => $this->item->id,
            'price' => 15.99,
        ];

        $response = $this->postJson('/api/admin/variants', $variantData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function price_must_be_numeric()
    {
        Sanctum::actingAs($this->vendor);

        $variantData = [
            'item_id' => $this->item->id,
            'name' => 'Large',
            'price' => 'invalid',
        ];

        $response = $this->postJson('/api/admin/variants', $variantData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function can_show_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->item->id]);

        $response = $this->getJson('/api/admin/variants/' . $variant->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $variant->id)
            ->assertJsonPath('name', $variant->name);
    }

    /** @test */
    public function cannot_show_other_vendors_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->otherItem->id]);

        $response = $this->getJson('/api/admin/variants/' . $variant->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->item->id]);

        $updateData = [
            'name' => 'Extra Large Updated',
            'price' => 25.99,
            'is_available' => 0,
        ];

        $response = $this->putJson('/api/admin/variants/' . $variant->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('variant.name', 'Extra Large Updated')
            ->assertJsonPath('variant.price', 25.99);

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'name' => 'Extra Large Updated',
            'price' => 25.99,
        ]);
    }

    /** @test */
    public function cannot_update_other_vendors_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->otherItem->id]);

        $updateData = ['name' => 'Updated'];

        $response = $this->putJson('/api/admin/variants/' . $variant->id, $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_variant_stock_management()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create([
            'item_id' => $this->item->id,
            'stock_management' => 0,
            'qty' => 10,
        ]);

        $updateData = [
            'stock_management' => 1,
            'qty' => 100,
            'min_order' => 2,
            'max_order' => 20,
        ];

        $response = $this->putJson('/api/admin/variants/' . $variant->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock_management' => 1,
            'qty' => 100,
            'min_order' => 2,
            'max_order' => 20,
        ]);
    }

    /** @test */
    public function can_delete_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->item->id]);

        $response = $this->deleteJson('/api/admin/variants/' . $variant->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Variant deleted successfully']);

        $this->assertDatabaseMissing('variants', ['id' => $variant->id]);
    }

    /** @test */
    public function cannot_delete_other_vendors_variant()
    {
        Sanctum::actingAs($this->vendor);

        $variant = Variants::factory()->create(['item_id' => $this->otherItem->id]);

        $response = $this->deleteJson('/api/admin/variants/' . $variant->id);

        $response->assertStatus(404);

        $this->assertDatabaseHas('variants', ['id' => $variant->id]);
    }

    /** @test */
    public function can_update_multiple_variants_for_same_item()
    {
        Sanctum::actingAs($this->vendor);

        $variant1 = Variants::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Small'
        ]);
        $variant2 = Variants::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Large'
        ]);

        $response = $this->getJson('/api/admin/variants?item_id=' . $this->item->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
