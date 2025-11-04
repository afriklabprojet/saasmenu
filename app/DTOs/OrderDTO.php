<?php

namespace App\DTOs;

use App\ValueObjects\Money;
use App\ValueObjects\OrderStatus;
use JsonSerializable;

/**
 * DTO for Order data transfer
 * Immutable data structure for passing order information between layers
 */
class OrderDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $orderNumber,
        public readonly int $vendorId,
        public readonly ?int $customerId,
        public readonly OrderStatus $status,
        public readonly Money $subtotal,
        public readonly Money $taxAmount,
        public readonly Money $deliveryFee,
        public readonly Money $discountAmount,
        public readonly Money $total,
        public readonly string $paymentMethod,
        public readonly string $paymentStatus,
        public readonly string $deliveryType,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly ?string $customerEmail,
        public readonly ?string $deliveryAddress,
        public readonly ?string $notes,
        public readonly ?string $scheduledAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly array $items = [],
        public readonly array $customer = [],
        public readonly array $vendor = []
    ) {}

    /**
     * Create from Eloquent model
     */
    public static function fromModel($order): self
    {
        return new self(
            id: $order->id,
            orderNumber: $order->order_number ?? 'ORD-' . $order->id,
            vendorId: $order->vendor_id,
            customerId: $order->user_id,
            status: OrderStatus::fromString($order->status ?? 'pending'),
            subtotal: Money::fromString($order->sub_total ?? 0),
            taxAmount: Money::fromString($order->tax ?? 0),
            deliveryFee: Money::fromString($order->delivery_charge ?? 0),
            discountAmount: Money::fromString($order->discount_amount ?? 0),
            total: Money::fromString($order->grand_total ?? 0),
            paymentMethod: $order->payment_type ?? 'cash',
            paymentStatus: $order->payment_status ?? 'pending',
            deliveryType: $order->delivery_type ?? 'pickup',
            customerName: $order->customer_name ?? $order->name ?? '',
            customerPhone: $order->customer_phone ?? $order->mobile ?? '',
            customerEmail: $order->customer_email ?? $order->email,
            deliveryAddress: $order->delivery_address ?? $order->address,
            notes: $order->notes ?? $order->note,
            scheduledAt: $order->scheduled_at?->toISOString(),
            createdAt: $order->created_at->toISOString(),
            updatedAt: $order->updated_at->toISOString(),
            items: isset($order->orderDetails) ?
                array_map(fn($item) => OrderItemDTO::fromModel($item), $order->orderDetails->toArray()) : [],
            customer: isset($order->customer) ? CustomerDTO::fromModel($order->customer)->toArray() : [],
            vendor: isset($order->vendor) ? VendorDTO::fromModel($order->vendor)->toArray() : []
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            orderNumber: $data['order_number'] ?? 'ORD-' . $data['id'],
            vendorId: $data['vendor_id'],
            customerId: $data['customer_id'] ?? null,
            status: OrderStatus::fromString($data['status'] ?? 'pending'),
            subtotal: Money::fromString($data['subtotal'] ?? 0),
            taxAmount: Money::fromString($data['tax_amount'] ?? 0),
            deliveryFee: Money::fromString($data['delivery_fee'] ?? 0),
            discountAmount: Money::fromString($data['discount_amount'] ?? 0),
            total: Money::fromString($data['total'] ?? 0),
            paymentMethod: $data['payment_method'] ?? 'cash',
            paymentStatus: $data['payment_status'] ?? 'pending',
            deliveryType: $data['delivery_type'] ?? 'pickup',
            customerName: $data['customer_name'] ?? '',
            customerPhone: $data['customer_phone'] ?? '',
            customerEmail: $data['customer_email'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            notes: $data['notes'] ?? null,
            scheduledAt: $data['scheduled_at'] ?? null,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            items: array_map(fn($item) => OrderItemDTO::fromArray($item), $data['items'] ?? []),
            customer: $data['customer'] ?? [],
            vendor: $data['vendor'] ?? []
        );
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->paymentStatus === 'paid';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if order is deliverable
     */
    public function isDeliverable(): bool
    {
        return $this->deliveryType === 'delivery' && !empty($this->deliveryAddress);
    }

    /**
     * Get total items count
     */
    public function getTotalItemsCount(): int
    {
        return array_sum(array_map(fn($item) => $item->quantity, $this->items));
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(): string
    {
        return $this->total->format();
    }

    /**
     * Check if order has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discountAmount->isPositive();
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->orderNumber,
            'vendor_id' => $this->vendorId,
            'customer_id' => $this->customerId,
            'status' => $this->status->getValue(),
            'status_label' => $this->status->getLabel(),
            'subtotal' => $this->subtotal->getAmount(),
            'tax_amount' => $this->taxAmount->getAmount(),
            'delivery_fee' => $this->deliveryFee->getAmount(),
            'discount_amount' => $this->discountAmount->getAmount(),
            'total' => $this->total->getAmount(),
            'formatted_total' => $this->total->format(),
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'delivery_type' => $this->deliveryType,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'delivery_address' => $this->deliveryAddress,
            'notes' => $this->notes,
            'scheduled_at' => $this->scheduledAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'customer' => $this->customer,
            'vendor' => $this->vendor,
            'is_paid' => $this->isPaid(),
            'is_pending' => $this->isPending(),
            'is_deliverable' => $this->isDeliverable(),
            'total_items_count' => $this->getTotalItemsCount(),
            'has_discount' => $this->hasDiscount(),
        ];
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
