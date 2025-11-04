<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    protected Order $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Get orders for a specific vendor with filters
     */
    public function getVendorOrders(int $vendorId, array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->where('vendor_id', $vendorId)
            ->with(['customer', 'orderDetails.item', 'vendor']);

        // Apply filters
        if (isset($filters['status_type'])) {
            if (is_array($filters['status_type'])) {
                $query->whereIn('status_type', $filters['status_type']);
            } else {
                $query->where('status_type', $filters['status_type']);
            }
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->orderByDesc('id')->get();
    }

    /**
     * Get orders with pagination
     */
    public function getPaginatedOrders(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->where('vendor_id', $vendorId)
            ->with(['customer', 'orderDetails.item']);

        // Apply same filters as getVendorOrders
        if (isset($filters['status_type'])) {
            if (is_array($filters['status_type'])) {
                $query->whereIn('status_type', $filters['status_type']);
            } else {
                $query->where('status_type', $filters['status_type']);
            }
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get order by ID with relationships
     */
    public function getOrderWithDetails(int $orderId, int $vendorId): ?object
    {
        return $this->model->newQuery()
            ->where('id', $orderId)
            ->where('vendor_id', $vendorId)
            ->with([
                'customer',
                'orderDetails.item',
                'vendor',
                'deliveryArea',
                'promoCode'
            ])
            ->first();
    }

    /**
     * Get orders by status for vendor
     */
    public function getOrdersByStatus(int $vendorId, array $statusTypes): Collection
    {
        return $this->model->newQuery()
            ->where('vendor_id', $vendorId)
            ->whereIn('status_type', $statusTypes)
            ->with(['customer', 'orderDetails.item'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, int $vendorId, int $status): bool
    {
        $updated = $this->model->newQuery()
            ->where('id', $orderId)
            ->where('vendor_id', $vendorId)
            ->update([
                'status_type' => $status,
                'updated_at' => now()
            ]);

        // Clear cache after update
        Cache::forget("vendor_{$vendorId}_order_stats");

        return $updated > 0;
    }

    /**
     * Get order statistics for vendor
     */
    public function getOrderStatistics(int $vendorId, ?string $period = null): array
    {
        $cacheKey = "vendor_{$vendorId}_order_stats_" . ($period ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($vendorId, $period) {
            $query = $this->model->newQuery()->where('vendor_id', $vendorId);

            // Apply period filter
            if ($period) {
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                }
            }

            return [
                'total_orders' => $query->count(),
                'total_revenue' => $query->sum('grand_total'),
                'pending_orders' => $query->where('status_type', 1)->count(),
                'confirmed_orders' => $query->where('status_type', 2)->count(),
                'completed_orders' => $query->where('status_type', 3)->count(),
                'cancelled_orders' => $query->where('status_type', 4)->count(),
                'average_order_value' => $query->avg('grand_total'),
            ];
        });
    }

    /**
     * Get recent orders for vendor
     */
    public function getRecentOrders(int $vendorId, int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->where('vendor_id', $vendorId)
            ->with(['customer', 'orderDetails'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new order
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * Find order by ID
     */
    public function find(int $orderId): ?object
    {
        return $this->model->find($orderId);
    }

    /**
     * Update order by ID
     */
    public function update(int $orderId, array $data): ?object
    {
        $order = $this->find($orderId);
        if ($order) {
            $order->update($data);
            return $order->fresh();
        }
        return null;
    }

    /**
     * Get orders by date range
     */
    public function getOrdersByDateRange(int $vendorId, string $startDate, string $endDate): Collection
    {
        return $this->model->newQuery()
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'orderDetails.item'])
            ->orderByDesc('created_at')
            ->get();
    }
}
