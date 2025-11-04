<?php

namespace App\DTOs;

use App\ValueObjects\Money;
use JsonSerializable;

/**
 * DTO for Order Item data transfer
 */
class OrderItemDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly int $orderId,
        public readonly int $productId,
        public readonly string $productName,
        public readonly Money $unitPrice,
        public readonly int $quantity,
        public readonly Money $totalPrice,
        public readonly ?string $notes,
        public readonly array $variants = [],
        public readonly array $extras = [],
        public readonly ?string $productImage = null
    ) {}

    /**
     * Create from Eloquent model
     */
    public static function fromModel($item): self
    {
        return new self(
            id: $item->id,
            orderId: $item->order_id,
            productId: $item->item_id ?? $item->product_id,
            productName: $item->item_name ?? $item->product_name ?? '',
            unitPrice: Money::fromString((string)($item->price ?? 0)),
            quantity: $item->qty ?? $item->quantity ?? 1,
            totalPrice: Money::fromString((string)(($item->price ?? 0) * ($item->qty ?? $item->quantity ?? 1))),
            notes: $item->notes ?? $item->item_notes,
            variants: self::parseVariants($item->variants_id ?? '', $item->variants_name ?? '', $item->variants_price ?? ''),
            extras: self::parseExtras($item->extras_id ?? '', $item->extras_name ?? '', $item->extras_price ?? ''),
            productImage: $item->item_image ?? $item->product_image
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            orderId: $data['order_id'],
            productId: $data['product_id'],
            productName: $data['product_name'] ?? '',
            unitPrice: Money::fromString($data['unit_price'] ?? 0),
            quantity: $data['quantity'] ?? 1,
            totalPrice: Money::fromString($data['total_price'] ?? 0),
            notes: $data['notes'] ?? null,
            variants: $data['variants'] ?? [],
            extras: $data['extras'] ?? [],
            productImage: $data['product_image'] ?? null
        );
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPrice(): string
    {
        return $this->unitPrice->format();
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPrice(): string
    {
        return $this->totalPrice->format();
    }

    /**
     * Check if item has variants
     */
    public function hasVariants(): bool
    {
        return !empty($this->variants);
    }

    /**
     * Check if item has extras
     */
    public function hasExtras(): bool
    {
        return !empty($this->extras);
    }

    /**
     * Get total extras price
     */
    public function getExtrasPrice(): Money
    {
        $total = 0;
        foreach ($this->extras as $extra) {
            $total += $extra['price'] ?? 0;
        }
        return Money::fromString((string)$total);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'unit_price' => $this->unitPrice->getAmount(),
            'formatted_unit_price' => $this->unitPrice->format(),
            'quantity' => $this->quantity,
            'total_price' => $this->totalPrice->getAmount(),
            'formatted_total_price' => $this->totalPrice->format(),
            'notes' => $this->notes,
            'variants' => $this->variants,
            'extras' => $this->extras,
            'product_image' => $this->productImage,
            'has_variants' => $this->hasVariants(),
            'has_extras' => $this->hasExtras(),
            'extras_price' => $this->getExtrasPrice()->getAmount()
        ];
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Parse variants from string format
     */
    private static function parseVariants(string $ids, string $names, string $prices): array
    {
        if (empty($ids) || empty($names)) {
            return [];
        }

        $idArray = explode(',', $ids);
        $nameArray = explode(',', $names);
        $priceArray = explode(',', $prices);

        $variants = [];
        for ($i = 0; $i < count($idArray); $i++) {
            if (isset($nameArray[$i])) {
                $variants[] = [
                    'id' => intval($idArray[$i]),
                    'name' => trim($nameArray[$i]),
                    'price' => floatval($priceArray[$i] ?? 0)
                ];
            }
        }

        return $variants;
    }

    /**
     * Parse extras from string format
     */
    private static function parseExtras(string $ids, string $names, string $prices): array
    {
        if (empty($ids) || empty($names)) {
            return [];
        }

        $idArray = explode(',', $ids);
        $nameArray = explode(',', $names);
        $priceArray = explode(',', $prices);

        $extras = [];
        for ($i = 0; $i < count($idArray); $i++) {
            if (isset($nameArray[$i])) {
                $extras[] = [
                    'id' => intval($idArray[$i]),
                    'name' => trim($nameArray[$i]),
                    'price' => floatval($priceArray[$i] ?? 0)
                ];
            }
        }

        return $extras;
    }
}
