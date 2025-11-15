<?php

namespace Tests\Feature\Admin\Api;

use App\Models\Category;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoriesApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $employee;
    protected User $otherVendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor user (type 2)
        $this->vendor = User::factory()->create(['type' => 2]);

        // Create employee user (type 4)
        $this->employee = User::factory()->create([
            'type' => 4,
            'vendor_id' => $this->vendor->id
        ]);

        // Create another vendor for authorization tests
        $this->otherVendor = User::factory()->create(['type' => 2]);

        Storage::fake('public');
    }

    /** @test */
    public function vendor_can_list_all_categories()
    {
        Category::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'categories' => [
                        '*' => ['id', 'name', 'slug', 'image', 'is_available']
                    ]
                ],
                'meta' => ['total']
            ])
            ->assertJsonPath('meta.total', 3);
    }

    /** @test */
    public function employee_can_list_vendor_categories()
    {
        Category::factory()->count(2)->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->employee)
            ->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    /** @test */
    public function vendor_can_create_category_with_valid_data()
    {
        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'name' => 'Test Category',
                'description' => 'Test description',
                'is_available' => 1,
                'reorder_id' => 1
            ]);

        if ($response->status() !== 201) {
            dump($response->json());
        }

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully'
            ])
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug']
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'vendor_id' => $this->vendor->id
        ]);
    }

    /** @test */
    public function vendor_can_create_category_with_image()
    {
        $image = UploadedFile::fake()->image('category.jpg');

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'name' => 'Category with Image',
                'image' => $image,
                'is_available' => 1
            ]);

        $response->assertStatus(201);

        $category = Category::where('name', 'Category with Image')->first();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists('admin-assets/images/category/' . $category->image);
    }

    /** @test */
    public function category_name_is_required()
    {
        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'description' => 'Test description'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function category_name_cannot_exceed_255_characters()
    {
        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'name' => str_repeat('a', 256)
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function image_must_be_valid_format()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'name' => 'Test Category',
                'image' => $invalidFile
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function image_size_cannot_exceed_2mb()
    {
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->actingAs($this->vendor)
            ->postJson('/api/admin/categories', [
                'name' => 'Test Category',
                'image' => $largeImage
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function vendor_can_view_single_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'slug', 'description']
            ])
            ->assertJsonPath('data.id', $category->id);
    }

    /** @test */
    public function vendor_cannot_view_other_vendor_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->otherVendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Category not found'
            ]);
    }

    /** @test */
    public function returns_404_for_non_existent_category()
    {
        $response = $this->actingAs($this->vendor)
            ->getJson('/api/admin/categories/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Category not found'
            ]);
    }

    /** @test */
    public function vendor_can_update_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Original Name',
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/categories/{$category->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function vendor_can_update_category_image()
    {
        $oldImage = UploadedFile::fake()->image('old.jpg');

        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        // Store old image
        $oldImageName = 'category-old.jpg';
        Storage::disk('public')->put('admin-assets/images/category/' . $oldImageName, $oldImage->get());
        $category->update(['image' => $oldImageName]);

        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/categories/{$category->id}", [
                'image' => $newImage
            ]);

        $response->assertStatus(200);

        $category->refresh();
        $this->assertNotEquals($oldImageName, $category->image);
        Storage::disk('public')->assertExists('admin-assets/images/category/' . $category->image);
    }

    /** @test */
    public function vendor_cannot_update_other_vendor_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->otherVendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/admin/categories/{$category->id}", [
                'name' => 'Hacked Name'
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function vendor_can_delete_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /** @test */
    public function deleting_category_unlinks_items()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        // Create items linked to this category
        $item1 = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id
        ]);

        $item2 = Item::factory()->create([
            'vendor_id' => $this->vendor->id,
            'cat_id' => $category->id
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200);

        // Verify items are unlinked
        $this->assertDatabaseHas('items', [
            'id' => $item1->id,
            'cat_id' => null
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item2->id,
            'cat_id' => null
        ]);
    }

    /** @test */
    public function deleting_category_removes_image()
    {
        $image = UploadedFile::fake()->image('category.jpg');
        $imageName = 'category-test.jpg';

        Storage::disk('public')->put('admin-assets/images/category/' . $imageName, $image->get());

        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'image' => $imageName,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200);
        Storage::disk('public')->assertMissing('admin-assets/images/category/' . $imageName);
    }

    /** @test */
    public function vendor_cannot_delete_other_vendor_category()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->otherVendor->id,
            'is_deleted' => 2
        ]);

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_endpoints()
    {
        $category = Category::factory()->create([
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 2
        ]);

        // Test all endpoints
        $this->getJson('/api/admin/categories')->assertStatus(401);
        $this->postJson('/api/admin/categories', ['name' => 'Test'])->assertStatus(401);
        $this->getJson("/api/admin/categories/{$category->id}")->assertStatus(401);
        $this->putJson("/api/admin/categories/{$category->id}", ['name' => 'Test'])->assertStatus(401);
        $this->deleteJson("/api/admin/categories/{$category->id}")->assertStatus(401);
    }
}
