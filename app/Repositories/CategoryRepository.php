<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected Category $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get categories for vendor with items (eager loading to fix N+1)
     * This fixes the N+1 query problem identified in the audit
     */
    public function getCategoriesWithItems(int $vendorId): Collection
    {
        $cacheKey = "vendor_{$vendorId}_categories_with_items";

        return Cache::remember($cacheKey, 1800, function () use ($vendorId) {
            return $this->model->newQuery()
                ->where('vendor_id', $vendorId)
                ->where('is_available', 1)
                ->with([
                    'items' => function ($query) {
                        $query->where('is_available', 1)
                              ->orderBy('reorder_id');
                    },
                    'items.variations',
                    'items.extras'
                ])
                ->orderBy('reorder_id')
                ->get();
        });
    }

    /**
     * Get active categories for vendor
     */
    public function getActiveCategories(int $vendorId): Collection
    {
        $cacheKey = "vendor_{$vendorId}_active_categories";

        return Cache::remember($cacheKey, 3600, function () use ($vendorId) {
            return $this->model->newQuery()
                ->where('vendor_id', $vendorId)
                ->where('is_available', 1)
                ->orderBy('reorder_id')
                ->get();
        });
    }

    /**
     * Get category by ID with items
     */
    public function getCategoryWithItems(int $categoryId, int $vendorId): ?object
    {
        return $this->model->newQuery()
            ->where('id', $categoryId)
            ->where('vendor_id', $vendorId)
            ->with([
                'items' => function ($query) {
                    $query->where('is_available', 1)
                          ->orderBy('reorder_id');
                },
                'items.variations',
                'items.extras'
            ])
            ->first();
    }

    /**
     * Reorder categories
     */
    public function reorderCategories(int $vendorId, array $orderData): bool
    {
        try {
            foreach ($orderData as $index => $categoryId) {
                $this->model->newQuery()
                    ->where('id', $categoryId)
                    ->where('vendor_id', $vendorId)
                    ->update(['reorder_id' => $index + 1]);
            }

            // Clear cache after reordering
            Cache::forget("vendor_{$vendorId}_categories_with_items");
            Cache::forget("vendor_{$vendorId}_active_categories");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Find category/vendor by ID
     */
    public function find(int $id): ?object
    {
        return $this->model->find($id);
    }

    /**
     * Get vendors within radius of coordinates
     * This is a simplified implementation - you may need to adjust based on your vendor model structure
     */
    public function getVendorsWithinRadius(float $latitude, float $longitude, float $radiusKm): Collection
    {
        // Assuming vendors have latitude and longitude columns
        // This uses the Haversine formula for distance calculation
        $results = DB::table('vendors')
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->having('distance', '<=', $radiusKm)
            ->where('is_available', 1)
            ->where('is_deleted', 2)
            ->orderBy('distance')
            ->get();

        // Convert to Eloquent Collection to match return type
        return new Collection($results->toArray());
    }
}
