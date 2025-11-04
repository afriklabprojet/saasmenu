<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Get orders for a specific vendor with filters
     */
    public function getVendorOrders(int $vendorId, array $filters = []): Collection;

    /**
     * Get orders with pagination
     */
    public function getPaginatedOrders(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get order by ID with relationships
     */
    public function getOrderWithDetails(int $orderId, int $vendorId): ?object;

    /**
     * Get orders by status for vendor
     */
    public function getOrdersByStatus(int $vendorId, array $statusTypes): Collection;

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, int $vendorId, int $status): bool;

    /**
     * Get order statistics for vendor
     */
    public function getOrderStatistics(int $vendorId, ?string $period = null): array;

    /**
     * Get recent orders for vendor
     */
    public function getRecentOrders(int $vendorId, int $limit = 10): Collection;

    /**
     * Create a new order
     */
    public function create(array $data): object;

    /**
     * Find order by ID
     */
    public function find(int $orderId): ?object;

    /**
     * Update order by ID
     */
    public function update(int $orderId, array $data): ?object;

    /**
     * Get orders by date range
     */
    public function getOrdersByDateRange(int $vendorId, string $startDate, string $endDate): Collection;
}
