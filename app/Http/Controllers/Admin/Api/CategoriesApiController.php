<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

/**
 * @group Categories Management
 *
 * API endpoints for managing restaurant categories
 */
class CategoriesApiController extends Controller
{
    /**
     * Display a listing of categories
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $categories = Category::where('vendor_id', $vendorId)
            ->where('is_deleted', 2)
            ->orderBy('reorder_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => new CategoryCollection($categories),
            'meta' => [
                'total' => $categories->count(),
            ]
        ]);
    }

    /**
     * Store a newly created category
     *
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        // Generate unique slug
        $slug = Str::slug($request->name) . '-' . Str::random(5);

        $category = new Category();
        $category->vendor_id = $vendorId;
        $category->name = $request->name;
        $category->slug = $slug;
        $category->description = $request->description;
        $category->is_available = $request->is_available ?? 1;
        $category->reorder_id = $request->reorder_id ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = 'category-' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('admin-assets/images/category', $imageName, 'public');
            $category->image = $imageName;
        }

        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    /**
     * Display the specified category
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $category = Category::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 2)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Update the specified category
     *
     * @param UpdateCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $category = Category::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 2)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Update basic fields
        $category->name = $request->name ?? $category->name;
        $category->description = $request->description ?? $category->description;
        $category->is_available = $request->is_available ?? $category->is_available;
        $category->reorder_id = $request->reorder_id ?? $category->reorder_id;

        // Update slug if name changed
        if ($request->has('name') && $request->name !== $category->name) {
            $category->slug = Str::slug($request->name) . '-' . Str::random(5);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete('admin-assets/images/category/' . $category->image);
            }

            $imageName = 'category-' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('admin-assets/images/category', $imageName, 'public');
            $category->image = $imageName;
        }

        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Remove the specified category
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $category = Category::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 2)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Unlink items from this category
        Item::where('cat_id', $category->id)
            ->where('vendor_id', $vendorId)
            ->update(['cat_id' => null]);

        // Delete category image
        if ($category->image) {
            Storage::disk('public')->delete('admin-assets/images/category/' . $category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }

    /**
     * Get vendor ID (handles both vendor and employee types)
     *
     * @return int
     */
    protected function getVendorId(): int
    {
        return Auth::user()->type == 4
            ? Auth::user()->vendor_id
            : Auth::user()->id;
    }
}
