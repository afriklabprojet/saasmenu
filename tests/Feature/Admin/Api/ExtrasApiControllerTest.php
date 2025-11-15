<?php

namespace Tests\Feature\Admin\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Extra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExtrasApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor
        $this->vendor = User::factory()->create(['type' => 2]);

        // Create category
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id
        ]);

        // Create item
        $this->item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id,
            'is_deleted' => 0
        ]);
    }

    /** @test */
    public function vendor_can_list_all_extras()
    {
        // Create extras for this vendor's item
        Extra::factory()->count(3)->create([
            'item_id' => $this->item->id
        ]);

        // Create extra for another vendor (should not appear)
        $otherItem = Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id
        ]);
        Extra::factory()->create(['item_id' => $otherItem->id]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/extras');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'extras' => [
                        '*' => ['id', 'name', 'price', 'item_id', 'is_available']
                    ]
                ],
                'meta'
            ])
            ->assertJsonCount(3, 'data.extras');
    }

    /** @test */
    public function vendor_can_filter_extras_by_item()
    {
        // Create extras for this item
        Extra::factory()->count(2)->create(['item_id' => $this->item->id]);

        // Create another item with extras
        $item2 = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 0
        ]);
        Extra::factory()->count(3)->create(['item_id' => $item2->id]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/extras?item_id={$this->item->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.extras');
    }

    /** @test */
    public function vendor_can_search_extras_by_name()
    {
        Extra::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Extra Cheese'
        ]);
        Extra::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Extra Bacon'
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/extras?search=Cheese');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.extras')
            ->assertJsonFragment(['name' => 'Extra Cheese']);
    }

    /** @test */
    public function vendor_can_create_extra_with_valid_data()
    {
        $extraData = [
            'item_id' => $this->item->id,
            'name' => 'Extra Sauce',
            'price' => 2.50,
            'is_available' => 1,
            'reorder_id' => 1
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Extra created successfully'
            ])
            ->assertJsonPath('data.name', 'Extra Sauce')
            ->assertJsonPath('data.price', '2.50');

        $this->assertDatabaseHas('extras', [
            'item_id' => $this->item->id,
            'name' => 'Extra Sauce',
            'price' => 2.50
        ]);
    }

    /** @test */
    public function extra_name_is_required()
    {
        $extraData = [
            'item_id' => $this->item->id,
            'price' => 2.50
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function extra_item_id_is_required()
    {
        $extraData = [
            'name' => 'Extra Sauce',
            'price' => 2.50
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_id']);
    }

    /** @test */
    public function extra_price_is_required()
    {
        $extraData = [
            'item_id' => $this->item->id,
            'name' => 'Extra Sauce'
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function extra_price_must_be_positive()
    {
        $extraData = [
            'item_id' => $this->item->id,
            'name' => 'Extra Sauce',
            'price' => -5
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function cannot_create_extra_for_other_vendor_item()
    {
        $otherItem = Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id
        ]);

        $extraData = [
            'item_id' => $otherItem->id,
            'name' => 'Extra Sauce',
            'price' => 2.50
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/extras', $extraData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_id']);
    }

    /** @test */
    public function vendor_can_view_single_extra()
    {
        $extra = Extra::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Extra Cheese'
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/extras/{$extra->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true
            ])
            ->assertJsonPath('data.id', $extra->id)
            ->assertJsonPath('data.name', 'Extra Cheese');
    }

    /** @test */
    public function vendor_cannot_view_other_vendor_extra()
    {
        $otherItem = Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id
        ]);
        $extra = Extra::factory()->create(['item_id' => $otherItem->id]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/extras/{$extra->id}");

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Extra not found'
            ]);
    }

    /** @test */
    public function returns_404_for_non_existent_extra()
    {
        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/extras/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function vendor_can_update_extra()
    {
        $extra = Extra::factory()->create([
            'item_id' => $this->item->id,
            'name' => 'Old Name',
            'price' => 1.00
        ]);

        $updateData = [
            'name' => 'New Name',
            'price' => 3.00
        ];

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/extras/{$extra->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Extra updated successfully'
            ]);

        $this->assertDatabaseHas('extras', [
            'id' => $extra->id,
            'name' => 'New Name',
            'price' => 3.00
        ]);
    }

    /** @test */
    public function vendor_cannot_update_other_vendor_extra()
    {
        $otherItem = Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id
        ]);
        $extra = Extra::factory()->create(['item_id' => $otherItem->id]);

        $updateData = ['name' => 'Hacked Name'];

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/extras/{$extra->id}", $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function vendor_can_delete_extra()
    {
        $extra = Extra::factory()->create(['item_id' => $this->item->id]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/extras/{$extra->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Extra deleted successfully'
            ]);

        $this->assertDatabaseMissing('extras', ['id' => $extra->id]);
    }

    /** @test */
    public function vendor_cannot_delete_other_vendor_extra()
    {
        $otherItem = Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id
        ]);
        $extra = Extra::factory()->create(['item_id' => $otherItem->id]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/extras/{$extra->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('extras', ['id' => $extra->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_endpoints()
    {
        $extra = Extra::factory()->create(['item_id' => $this->item->id]);

        // Test all endpoints
        $this->getJson('/api/admin/extras')->assertStatus(401);
        $this->getJson("/api/admin/extras/{$extra->id}")->assertStatus(401);
        $this->postJson('/api/admin/extras', [])->assertStatus(401);
        $this->putJson("/api/admin/extras/{$extra->id}", [])->assertStatus(401);
        $this->deleteJson("/api/admin/extras/{$extra->id}")->assertStatus(401);
    }
}
