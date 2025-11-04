<?php

namespace App\DTOs;

use App\ValueObjects\Money;
use JsonSerializable;

/**
 * DTO for Product data transfer
 */
class ProductDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly int $categoryId,
        public readonly string $categoryName,
        public readonly int $vendorId,
        public readonly Money $price,
        public readonly ?Money $originalPrice,
        public readonly string $status,
        public readonly bool $isFeatured,
        public readonly bool $isAvailable,
        public readonly ?int $preparationTime,
        public readonly ?int $calories,
        public readonly ?string $allergens,
        public readonly ?string $ingredients,
        public readonly ?float $weight,
        public readonly ?string $spiceLevel,
        public readonly array $dietaryInfo,
        public readonly ?string $image,
        public readonly array $galleryImages,
        public readonly array $variants,
        public readonly array $extras,
        public readonly float $rating,
        public readonly int $reviewsCount,
        public readonly int $orderCount,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    /**
     * Create from Eloquent model
     */
    public static function fromModel($product): self
    {
        return new self(
            id: $product->id,
            name: $product->item_name ?? $product->name ?? '',
            description: $product->item_description ?? $product->description ?? '',
            categoryId: $product->category_id ?? 0,
            categoryName: $product->category?->category_name ?? '',
            vendorId: $product->vendor_id ?? 0,
            price: Money::fromString($product->item_price ?? $product->price ?? 0),
            originalPrice: isset($product->item_original_price) ?
                Money::fromString($product->item_original_price) : null,
            status: $product->product_status ?? $product->status ?? 'available',
            isFeatured: $product->is_featured ?? false,
            isAvailable: $product->is_available ?? true,
            preparationTime: $product->preparation_time,
            calories: $product->calories,
            allergens: $product->allergens,
            ingredients: $product->ingredients,
            weight: $product->weight,
            spiceLevel: $product->spice_level,
            dietaryInfo: self::parseDietaryInfo($product->dietary_info ?? ''),
            image: $product->item_image ?? $product->image,
            galleryImages: self::parseGalleryImages($product->gallery_images ?? []),
            variants: self::parseVariants($product->variants ?? []),
            extras: self::parseExtras($product->extras ?? []),
            rating: floatval($product->rating ?? 0),
            reviewsCount: $product->reviews_count ?? 0,
            orderCount: $product->order_count ?? 0,
            createdAt: $product->created_at->toISOString(),
            updatedAt: $product->updated_at->toISOString()
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            categoryId: $data['category_id'] ?? 0,
            categoryName: $data['category_name'] ?? '',
            vendorId: $data['vendor_id'] ?? 0,
            price: Money::fromString($data['price'] ?? 0),
            originalPrice: isset($data['original_price']) ?
                Money::fromString($data['original_price']) : null,
            status: $data['status'] ?? 'available',
            isFeatured: $data['is_featured'] ?? false,
            isAvailable: $data['is_available'] ?? true,
            preparationTime: $data['preparation_time'] ?? null,
            calories: $data['calories'] ?? null,
            allergens: $data['allergens'] ?? null,
            ingredients: $data['ingredients'] ?? null,
            weight: $data['weight'] ?? null,
            spiceLevel: $data['spice_level'] ?? null,
            dietaryInfo: $data['dietary_info'] ?? [],
            image: $data['image'] ?? null,
            galleryImages: $data['gallery_images'] ?? [],
            variants: $data['variants'] ?? [],
            extras: $data['extras'] ?? [],
            rating: floatval($data['rating'] ?? 0),
            reviewsCount: $data['reviews_count'] ?? 0,
            orderCount: $data['order_count'] ?? 0,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at']
        );
    }

    /**
     * Check if product has discount
     */
    public function hasDiscount(): bool
    {
        return $this->originalPrice !== null &&
               $this->originalPrice->greaterThan($this->price);
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if (!$this->hasDiscount()) {
            return 0;
        }

        $discount = $this->originalPrice->subtract($this->price);
        return ($discount->getAmount() / $this->originalPrice->getAmount()) * 100;
    }

    /**
     * Check if product is vegetarian
     */
    public function isVegetarian(): bool
    {
        return in_array('vegetarian', $this->dietaryInfo);
    }

    /**
     * Check if product is vegan
     */
    public function isVegan(): bool
    {
        return in_array('vegan', $this->dietaryInfo);
    }

    /**
     * Check if product is gluten free
     */
    public function isGlutenFree(): bool
    {
        return in_array('gluten_free', $this->dietaryInfo);
    }

    /**
     * Check if product is spicy
     */
    public function isSpicy(): bool
    {
        return in_array($this->spiceLevel, ['hot', 'very_hot']);
    }

    /**
     * Check if product has variants
     */
    public function hasVariants(): bool
    {
        return !empty($this->variants);
    }

    /**
     * Check if product has extras
     */
    public function hasExtras(): bool
    {
        return !empty($this->extras);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return $this->price->format();
    }

    /**
     * Get formatted original price
     */
    public function getFormattedOriginalPrice(): ?string
    {
        return $this->originalPrice?->format();
    }

    /**
     * Get lowest variant price
     */
    public function getLowestVariantPrice(): Money
    {
        if (empty($this->variants)) {
            return $this->price;
        }

        $lowest = $this->price;
        foreach ($this->variants as $variant) {
            $variantPrice = Money::fromString($variant['price'] ?? 0);
            $totalPrice = $this->price->add($variantPrice);

            if ($totalPrice->lessThan($lowest)) {
                $lowest = $totalPrice;
            }
        }

        return $lowest;
    }

    /**
     * Get highest variant price
     */
    public function getHighestVariantPrice(): Money
    {
        if (empty($this->variants)) {
            return $this->price;
        }

        $highest = $this->price;
        foreach ($this->variants as $variant) {
            $variantPrice = Money::fromString($variant['price'] ?? 0);
            $totalPrice = $this->price->add($variantPrice);

            if ($totalPrice->greaterThan($highest)) {
                $highest = $totalPrice;
            }
        }

        return $highest;
    }

    /**
     * Get price range string
     */
    public function getPriceRange(): string
    {
        if (!$this->hasVariants()) {
            return $this->getFormattedPrice();
        }

        $lowest = $this->getLowestVariantPrice();
        $highest = $this->getHighestVariantPrice();

        if ($lowest->equals($highest)) {
            return $lowest->format();
        }

        return $lowest->format() . ' - ' . $highest->format();
    }

    /**
     * Check if product has high rating
     */
    public function hasHighRating(): bool
    {
        return $this->rating >= 4.0;
    }

    /**
     * Check if product is popular
     */
    public function isPopular(): bool
    {
        return $this->orderCount >= 50;
    }

    /**
     * Get dietary labels for display
     */
    public function getDietaryLabels(): array
    {
        $labels = [];

        if ($this->isVegetarian()) $labels[] = 'Végétarien';
        if ($this->isVegan()) $labels[] = 'Vegan';
        if ($this->isGlutenFree()) $labels[] = 'Sans Gluten';
        if (in_array('halal', $this->dietaryInfo)) $labels[] = 'Halal';
        if (in_array('kosher', $this->dietaryInfo)) $labels[] = 'Kasher';

        return $labels;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'vendor_id' => $this->vendorId,
            'price' => $this->price->getAmount(),
            'formatted_price' => $this->price->format(),
            'original_price' => $this->originalPrice?->getAmount(),
            'formatted_original_price' => $this->originalPrice?->format(),
            'price_range' => $this->getPriceRange(),
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'is_available' => $this->isAvailable,
            'preparation_time' => $this->preparationTime,
            'calories' => $this->calories,
            'allergens' => $this->allergens,
            'ingredients' => $this->ingredients,
            'weight' => $this->weight,
            'spice_level' => $this->spiceLevel,
            'dietary_info' => $this->dietaryInfo,
            'dietary_labels' => $this->getDietaryLabels(),
            'image' => $this->image,
            'gallery_images' => $this->galleryImages,
            'variants' => $this->variants,
            'extras' => $this->extras,
            'rating' => $this->rating,
            'reviews_count' => $this->reviewsCount,
            'order_count' => $this->orderCount,
            'has_discount' => $this->hasDiscount(),
            'discount_percentage' => $this->getDiscountPercentage(),
            'has_variants' => $this->hasVariants(),
            'has_extras' => $this->hasExtras(),
            'has_high_rating' => $this->hasHighRating(),
            'is_popular' => $this->isPopular(),
            'is_vegetarian' => $this->isVegetarian(),
            'is_vegan' => $this->isVegan(),
            'is_gluten_free' => $this->isGlutenFree(),
            'is_spicy' => $this->isSpicy(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
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
     * Parse dietary info from string or array
     */
    private static function parseDietaryInfo($dietaryInfo): array
    {
        if (is_array($dietaryInfo)) {
            return $dietaryInfo;
        }

        if (is_string($dietaryInfo) && !empty($dietaryInfo)) {
            // Try JSON first
            $decoded = json_decode($dietaryInfo, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Fallback to comma-separated
            return array_map('trim', explode(',', $dietaryInfo));
        }

        return [];
    }

    /**
     * Parse gallery images
     */
    private static function parseGalleryImages($galleryImages): array
    {
        if (is_array($galleryImages)) {
            return $galleryImages;
        }

        if (is_string($galleryImages) && !empty($galleryImages)) {
            $decoded = json_decode($galleryImages, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Parse variants
     */
    private static function parseVariants($variants): array
    {
        if (is_array($variants)) {
            return $variants;
        }

        if (is_string($variants) && !empty($variants)) {
            $decoded = json_decode($variants, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Parse extras
     */
    private static function parseExtras($extras): array
    {
        if (is_array($extras)) {
            return $extras;
        }

        if (is_string($extras) && !empty($extras)) {
            $decoded = json_decode($extras, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
