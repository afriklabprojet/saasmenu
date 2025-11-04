<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get categories for vendor with items (eager loading to fix N+1)
     */
    public function getCategoriesWithItems(int $vendorId): Collection;

    /**
     * Get active categories for vendor
     */
    public function getActiveCategories(int $vendorId): Collection;

    /**
     * Get category by ID with items
     */
    public function getCategoryWithItems(int $categoryId, int $vendorId): ?object;

    /**
     * Reorder categories
     */
    public function reorderCategories(int $vendorId, array $orderData): bool;

    /**
     * Find category/vendor by ID
     */
    public function find(int $id): ?object;

    /**
     * Get vendors within radius of coordinates
     */
    public function getVendorsWithinRadius(float $latitude, float $longitude, float $radiusKm): Collection;
}
