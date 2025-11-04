<?php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\ProductDTO;
use App\DTOs\VendorDTO;
use App\ValueObjects\Money;
use App\ValueObjects\OrderStatus;
use App\ValueObjects\Email;
use App\ValueObjects\PhoneNumber;
use App\ValueObjects\Coordinates;
use App\Repositories\OrderRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Collection;

/**
 * Enhanced service using Value Objects and DTOs
 * Demonstrates type safety and domain logic encapsulation
 */
class EnhancedOrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CategoryRepository $categoryRepository
    ) {}

    /**
     * Create order with value objects validation
     */
    public function createOrder(array $orderData): OrderDTO
    {
        // Validate and create value objects
        $total = Money::fromString($orderData['total']);
        $status = OrderStatus::pending();
        $customerEmail = Email::fromString($orderData['customer_email']);
        $customerPhone = PhoneNumber::fromString($orderData['customer_phone']);

        // Validate delivery location if applicable
        if ($orderData['delivery_type'] === 'delivery') {
            $this->validateDeliveryLocation($orderData);
        }

        // Calculate order totals with business logic
        $calculatedTotal = $this->calculateOrderTotal($orderData['items'], $orderData);

        if (!$total->equals($calculatedTotal)) {
            throw new \InvalidArgumentException('Order total mismatch');
        }

        // Create order using repository
        $order = $this->orderRepository->create([
            'vendor_id' => $orderData['vendor_id'],
            'customer_email' => $customerEmail->getValue(),
            'customer_phone' => $customerPhone->getValue(),
            'status' => $status->getValue(),
            'grand_total' => $total->getAmount(),
            'payment_type' => $orderData['payment_method'],
            'delivery_type' => $orderData['delivery_type'],
            'customer_name' => $orderData['customer_name'],
            'delivery_address' => $orderData['delivery_address'] ?? null,
            'notes' => $orderData['notes'] ?? null,
        ]);

        return OrderDTO::fromModel($order);
    }

    /**
     * Update order status with business rules validation
     */
    public function updateOrderStatus(int $orderId, string $newStatus): OrderDTO
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $currentStatus = OrderStatus::fromString($order->status);
        $newStatusObj = OrderStatus::fromString($newStatus);

        // Validate status transition
        if (!$currentStatus->canTransitionTo($newStatusObj)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$currentStatus->getValue()} to {$newStatus}"
            );
        }

        // Update order
        $updatedOrder = $this->orderRepository->update($orderId, [
            'status' => $newStatus
        ]);

        return OrderDTO::fromModel($updatedOrder);
    }

    /**
     * Calculate delivery fee based on distance and vendor settings
     */
    public function calculateDeliveryFee(int $vendorId, string $deliveryAddress): Money
    {
        // In real implementation, you would:
        // 1. Get vendor coordinates and delivery settings
        // 2. Geocode the delivery address
        // 3. Calculate distance
        // 4. Apply vendor's delivery fee rules

        // Simplified example
        $baseDeliveryFee = Money::fromString('5.00');

        // Add distance-based surcharge (simplified)
        $distanceSurcharge = Money::fromString('2.00');

        return $baseDeliveryFee->add($distanceSurcharge);
    }

    /**
     * Get vendor recommendations based on location and preferences
     */
    public function getVendorRecommendations(
        Coordinates $customerLocation,
        array $preferences = []
    ): Collection {
        // Filter vendors by delivery radius
        $vendors = $this->categoryRepository->getVendorsWithinRadius(
            $customerLocation->getLatitude(),
            $customerLocation->getLongitude(),
            $preferences['max_distance'] ?? 10
        );

        // Apply filters using Value Objects
        return $vendors->filter(function ($vendor) use ($preferences) {
            $vendorDTO = VendorDTO::fromModel($vendor);

            // Filter by cuisine type
            if (!empty($preferences['cuisine_types'])) {
                $hasMatchingCuisine = !empty(array_intersect(
                    $vendorDTO->cuisineTypes,
                    $preferences['cuisine_types']
                ));
                if (!$hasMatchingCuisine) return false;
            }

            // Filter by minimum order amount
            if (isset($preferences['max_minimum_order'])) {
                $maxMinOrder = Money::fromString($preferences['max_minimum_order']);
                if ($vendorDTO->minimumOrder->greaterThan($maxMinOrder)) {
                    return false;
                }
            }

            // Filter by rating
            if (isset($preferences['min_rating'])) {
                if ($vendorDTO->rating < $preferences['min_rating']) {
                    return false;
                }
            }

            // Filter by availability
            if ($preferences['only_open'] ?? false) {
                if (!$vendorDTO->isCurrentlyOpen()) {
                    return false;
                }
            }

            return true;
        })->map(fn($vendor) => VendorDTO::fromModel($vendor));
    }

    /**
     * Calculate order analytics with type safety
     */
    public function calculateOrderAnalytics(int $vendorId, \DateTime $startDate, \DateTime $endDate): array
    {
        $orders = $this->orderRepository->getOrdersByDateRange(
            $vendorId,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        );

        $totalRevenue = Money::zero();
        $ordersByStatus = [];
        $averageOrderValue = Money::zero();
        $totalOrders = 0;

        foreach ($orders as $order) {
            $orderDTO = OrderDTO::fromModel($order);

            // Accumulate revenue
            $totalRevenue = $totalRevenue->add($orderDTO->total);

            // Count by status
            $status = $orderDTO->status->getValue();
            $ordersByStatus[$status] = ($ordersByStatus[$status] ?? 0) + 1;

            $totalOrders++;
        }

        // Calculate average order value
        if ($totalOrders > 0) {
            $averageOrderValue = $totalRevenue->divide($totalOrders);
        }

        return [
            'total_revenue' => $totalRevenue,
            'formatted_total_revenue' => $totalRevenue->format(),
            'total_orders' => $totalOrders,
            'average_order_value' => $averageOrderValue,
            'formatted_average_order_value' => $averageOrderValue->format(),
            'orders_by_status' => $ordersByStatus,
            'revenue_growth' => $this->calculateRevenueGrowth($vendorId, $startDate, $endDate),
        ];
    }

    /**
     * Validate delivery location using coordinates
     */
    private function validateDeliveryLocation(array $orderData): void
    {
        if (empty($orderData['delivery_coordinates'])) {
            throw new \InvalidArgumentException('Delivery coordinates required for delivery orders');
        }

        $deliveryLocation = Coordinates::fromString($orderData['delivery_coordinates']);

        // Get vendor location and delivery radius
        $vendor = $this->categoryRepository->find($orderData['vendor_id']);
        if (!$vendor || !$vendor->latitude || !$vendor->longitude) {
            throw new \InvalidArgumentException('Vendor location not available');
        }

        $vendorLocation = new Coordinates($vendor->latitude, $vendor->longitude);
        $deliveryRadius = $vendor->delivery_radius ?? 5;

        if (!$deliveryLocation->isWithinDeliveryRadius($vendorLocation, $deliveryRadius)) {
            throw new \InvalidArgumentException('Delivery location outside vendor radius');
        }
    }

    /**
     * Calculate order total with business logic
     */
    private function calculateOrderTotal(array $items, array $orderData): Money
    {
        $subtotal = Money::zero();

        // Calculate items subtotal
        foreach ($items as $item) {
            $itemPrice = Money::fromString($item['price']);
            $quantity = $item['quantity'];
            $itemTotal = $itemPrice->multiply($quantity);
            $subtotal = $subtotal->add($itemTotal);
        }

        // Add delivery fee
        $deliveryFee = Money::fromString($orderData['delivery_fee'] ?? 0);
        $total = $subtotal->add($deliveryFee);

        // Add tax
        if (isset($orderData['tax_rate'])) {
            $tax = $subtotal->percentage($orderData['tax_rate']);
            $total = $total->add($tax);
        }

        // Apply discount
        if (isset($orderData['discount_amount'])) {
            $discount = Money::fromString($orderData['discount_amount']);
            $total = $total->subtract($discount);
        }

        return $total;
    }

    /**
     * Calculate revenue growth (simplified)
     */
    private function calculateRevenueGrowth(int $vendorId, \DateTime $startDate, \DateTime $endDate): array
    {
        // Calculate previous period for comparison
        $periodDays = $startDate->diff($endDate)->days;
        $previousStart = (clone $startDate)->sub(new \DateInterval("P{$periodDays}D"));
        $previousEnd = (clone $endDate)->sub(new \DateInterval("P{$periodDays}D"));

        $currentRevenue = Money::zero();
        $previousRevenue = Money::zero();

        // Get current period revenue
        $currentOrders = $this->orderRepository->getOrdersByDateRange(
            $vendorId,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        );
        foreach ($currentOrders as $order) {
            $currentRevenue = $currentRevenue->add(Money::fromString($order->grand_total ?? 0));
        }

        // Get previous period revenue
        $previousOrders = $this->orderRepository->getOrdersByDateRange(
            $vendorId,
            $previousStart->format('Y-m-d H:i:s'),
            $previousEnd->format('Y-m-d H:i:s')
        );
        foreach ($previousOrders as $order) {
            $previousRevenue = $previousRevenue->add(Money::fromString($order->grand_total ?? 0));
        }

        // Calculate growth percentage
        $growthPercentage = 0;
        if ($previousRevenue->isPositive()) {
            $difference = $currentRevenue->subtract($previousRevenue);
            $growthPercentage = ($difference->getAmount() / $previousRevenue->getAmount()) * 100;
        }

        return [
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'growth_percentage' => round($growthPercentage, 2),
            'is_growing' => $growthPercentage > 0
        ];
    }
}
