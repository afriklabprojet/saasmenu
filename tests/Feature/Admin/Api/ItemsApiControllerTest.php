<?php

namespace Tests\Feature\Admin\Api;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $employee;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor user
        $this->vendor = User::factory()->create(['type' => 2]);

        // Create employee for this vendor
        $this->employee = User::factory()->create([
            'type' => 4,
            'vendor_id' => $this->vendor->id
        ]);

        // Create category for this vendor
        $this->category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);
    }

    /** @test */
    public function vendor_can_list_all_items()
    {
        // Create items for this vendor
        Item::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'is_deleted' => 0
        ]);

        // Create item for another vendor (should not appear)
        Item::factory()->create([
            'vendor_id' => User::factory()->create(['type' => 2])->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'price', 'cat_id', 'is_available']
                    ]
                ],
                'meta'
            ])
            ->assertJsonCount(3, 'data.items');
    }

    /** @test */
    public function employee_can_list_vendor_items()
    {
        Item::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->employee)
            ->getJson('/api/admin/items');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.items');
    }

    /** @test */
    public function can_filter_items_by_category()
    {
        $category2 = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        Item::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'is_deleted' => 0
        ]);

        Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category2->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/items?cat_id=' . $this->category->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.items');
    }

    /** @test */
    public function can_search_items_by_name()
    {
        Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'name' => 'Margherita Pizza',
            'is_deleted' => 0
        ]);

        Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'name' => 'Cheese Burger',
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/items?search=Pizza');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonFragment(['name' => 'Margherita Pizza']);
    }

    /** @test */
    public function vendor_can_create_item_with_valid_data()
    {
        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id,
            'description' => 'Test description',
            'price' => 25.99,
            'original_price' => 30.00,
            'is_available' => 1,
            'stock_management' => 1,
            'qty' => 100,
            'min_order' => 1,
            'max_order' => 10,
            'low_qty' => 10,
            'tax' => '5',
            'sku' => 'TEST-001',
            'reorder_id' => 1
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Item created successfully'
            ])
            ->assertJsonPath('data.name', 'Test Item');

        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'price' => 25.99
        ]);
    }

    /** @test */
    public function vendor_can_create_item_with_image()
    {
        Storage::fake('public');

        $itemData = [
            'name' => 'Item with Image',
            'cat_id' => $this->category->id,
            'price' => 15.99,
            'image' => UploadedFile::fake()->image('item.jpg', 400, 300)
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(201);

        $item = Item::where('name', 'Item with Image')->first();
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists('admin-assets/images/item/' . $item->image);
    }

    /** @test */
    public function item_name_is_required()
    {
        $itemData = [
            'cat_id' => $this->category->id,
            'price' => 10.00
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function item_category_is_required()
    {
        $itemData = [
            'name' => 'Test Item',
            'price' => 10.00
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cat_id']);
    }

    /** @test */
    public function item_price_is_required()
    {
        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function item_name_cannot_exceed_255_characters()
    {
        $itemData = [
            'name' => str_repeat('a', 256),
            'cat_id' => $this->category->id,
            'price' => 10.00
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function item_price_must_be_positive()
    {
        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id,
            'price' => -10.00
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function image_must_be_valid_format()
    {
        Storage::fake('public');

        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id,
            'price' => 10.00,
            'image' => UploadedFile::fake()->create('document.pdf', 100)
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function image_size_cannot_exceed_2mb()
    {
        Storage::fake('public');

        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id,
            'price' => 10.00,
            'image' => UploadedFile::fake()->image('large.jpg')->size(3000)
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function cannot_create_item_with_other_vendor_category()
    {
        $otherVendor = User::factory()->create(['type' => 2]);
        $otherCategory = Category::factory()->create([
            'vendor_id' => $otherVendor->id,
            'is_deleted' => 2
        ]);

        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $otherCategory->id,
            'price' => 10.00
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cat_id']);
    }

    /** @test */
    public function qty_is_required_when_stock_management_enabled()
    {
        $itemData = [
            'name' => 'Test Item',
            'cat_id' => $this->category->id,
            'price' => 10.00,
            'stock_management' => 1
        ];

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qty']);
    }

    /** @test */
    public function vendor_can_view_single_item()
    {
        $item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true
            ])
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.name', $item->name);
    }

    /** @test */
    public function vendor_cannot_view_other_vendor_item()
    {
        $otherVendor = User::factory()->create(['type' => 2]);
        $item = Item::factory()->create([
            'vendor_id' => $otherVendor->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/items/{$item->id}");

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Item not found'
            ]);
    }

    /** @test */
    public function returns_404_for_non_existent_item()
    {
        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/items/99999');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Item not found']);
    }

    /** @test */
    public function vendor_can_update_item()
    {
        $item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'name' => 'Old Name',
            'price' => 10.00,
            'is_deleted' => 0
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 15.99
        ];

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/items/{$item->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Name',
            'price' => 15.99
        ]);
    }

    /** @test */
    public function vendor_can_update_item_image()
    {
        Storage::fake('public');

        $item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'image' => 'old-image.jpg',
            'is_deleted' => 0
        ]);

        // Create old image
        Storage::disk('public')->put('admin-assets/images/item/old-image.jpg', 'old content');

        $updateData = [
            'image' => UploadedFile::fake()->image('new-image.jpg')
        ];

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/items/{$item->id}", $updateData);

        $response->assertStatus(200);

        $item->refresh();
        $this->assertNotEquals('old-image.jpg', $item->image);
        Storage::disk('public')->assertExists('admin-assets/images/item/' . $item->image);
        Storage::disk('public')->assertMissing('admin-assets/images/item/old-image.jpg');
    }

    /** @test */
    public function vendor_cannot_update_other_vendor_item()
    {
        $otherVendor = User::factory()->create(['type' => 2]);
        $item = Item::factory()->create([
            'vendor_id' => $otherVendor->id,
            'is_deleted' => 0
        ]);

        $updateData = ['name' => 'Hacked Name'];

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/items/{$item->id}", $updateData);

        $response->assertStatus(404);

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'name' => 'Hacked Name'
        ]);
    }

    /** @test */
    public function vendor_can_delete_item()
    {
        $item = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $this->category->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_deleted' => 1,
            'is_available' => 0
        ]);
    }

    /** @test */
    public function vendor_cannot_delete_other_vendor_item()
    {
        $otherVendor = User::factory()->create(['type' => 2]);
        $item = Item::factory()->create([
            'vendor_id' => $otherVendor->id,
            'is_deleted' => 0
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/items/{$item->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_deleted' => 0
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_endpoints()
    {
        $response = $this->getJson('/api/admin/items');
        $response->assertStatus(401);

        $response = $this->postJson('/api/admin/items', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/admin/items/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/admin/items/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/admin/items/1');
        $response->assertStatus(401);
    }
}
