<?php

namespace App\DTOs;

use App\ValueObjects\Email;
use App\ValueObjects\PhoneNumber;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Money;
use JsonSerializable;

/**
 * DTO for Vendor/Restaurant data transfer
 */
class VendorDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $restaurantName,
        public readonly string $slug,
        public readonly Email $email,
        public readonly PhoneNumber $phone,
        public readonly string $description,
        public readonly string $address,
        public readonly string $city,
        public readonly string $country,
        public readonly ?Coordinates $coordinates,
        public readonly array $cuisineTypes,
        public readonly Money $deliveryFee,
        public readonly Money $minimumOrder,
        public readonly int $estimatedDeliveryTime,
        public readonly float $deliveryRadius,
        public readonly bool $isAvailable,
        public readonly bool $acceptsCash,
        public readonly bool $acceptsCard,
        public readonly bool $acceptsOnline,
        public readonly ?string $logo,
        public readonly ?string $coverImage,
        public readonly array $businessHours,
        public readonly array $socialLinks,
        public readonly float $rating,
        public readonly int $reviewsCount,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly array $stats = []
    ) {}

    /**
     * Create from Eloquent model
     */
    public static function fromModel($vendor): self
    {
        return new self(
            id: $vendor->id,
            name: $vendor->name ?? '',
            restaurantName: $vendor->restaurant_name ?? $vendor->name ?? '',
            slug: $vendor->slug ?? '',
            email: Email::fromString($vendor->email ?? ''),
            phone: PhoneNumber::fromString($vendor->mobile ?? $vendor->phone ?? ''),
            description: $vendor->description ?? '',
            address: $vendor->address ?? '',
            city: $vendor->city ?? '',
            country: $vendor->country ?? '',
            coordinates: ($vendor->latitude && $vendor->longitude) ?
                new Coordinates($vendor->latitude, $vendor->longitude) : null,
            cuisineTypes: self::parseCuisineTypes($vendor->cuisine_type ?? ''),
            deliveryFee: Money::fromString($vendor->delivery_fee ?? 0),
            minimumOrder: Money::fromString($vendor->minimum_order ?? 0),
            estimatedDeliveryTime: $vendor->estimated_delivery_time ?? 30,
            deliveryRadius: floatval($vendor->delivery_radius ?? 5),
            isAvailable: $vendor->is_available ?? true,
            acceptsCash: $vendor->accepts_cash ?? true,
            acceptsCard: $vendor->accepts_card ?? false,
            acceptsOnline: $vendor->accepts_online ?? false,
            logo: $vendor->logo ?? $vendor->image,
            coverImage: $vendor->cover_image ?? $vendor->banner_image,
            businessHours: self::parseBusinessHours($vendor->business_hours ?? []),
            socialLinks: self::parseSocialLinks($vendor),
            rating: floatval($vendor->rating ?? 0),
            reviewsCount: $vendor->reviews_count ?? 0,
            createdAt: $vendor->created_at->toISOString(),
            updatedAt: $vendor->updated_at->toISOString(),
            stats: self::getVendorStats($vendor)
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
            restaurantName: $data['restaurant_name'] ?? '',
            slug: $data['slug'] ?? '',
            email: Email::fromString($data['email'] ?? ''),
            phone: PhoneNumber::fromString($data['phone'] ?? ''),
            description: $data['description'] ?? '',
            address: $data['address'] ?? '',
            city: $data['city'] ?? '',
            country: $data['country'] ?? '',
            coordinates: isset($data['latitude'], $data['longitude']) ?
                new Coordinates($data['latitude'], $data['longitude']) : null,
            cuisineTypes: $data['cuisine_types'] ?? [],
            deliveryFee: Money::fromString($data['delivery_fee'] ?? 0),
            minimumOrder: Money::fromString($data['minimum_order'] ?? 0),
            estimatedDeliveryTime: $data['estimated_delivery_time'] ?? 30,
            deliveryRadius: floatval($data['delivery_radius'] ?? 5),
            isAvailable: $data['is_available'] ?? true,
            acceptsCash: $data['accepts_cash'] ?? true,
            acceptsCard: $data['accepts_card'] ?? false,
            acceptsOnline: $data['accepts_online'] ?? false,
            logo: $data['logo'] ?? null,
            coverImage: $data['cover_image'] ?? null,
            businessHours: $data['business_hours'] ?? [],
            socialLinks: $data['social_links'] ?? [],
            rating: floatval($data['rating'] ?? 0),
            reviewsCount: $data['reviews_count'] ?? 0,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            stats: $data['stats'] ?? []
        );
    }

    /**
     * Check if vendor is currently open
     */
    public function isCurrentlyOpen(): bool
    {
        $now = new \DateTime();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        $todayHours = $this->businessHours[$currentDay] ?? null;

        if (!$todayHours || !($todayHours['is_open'] ?? false)) {
            return false;
        }

        $openTime = $todayHours['open_time'] ?? '00:00';
        $closeTime = $todayHours['close_time'] ?? '23:59';

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    /**
     * Check if delivery is available to coordinates
     */
    public function canDeliverTo(Coordinates $destination): bool
    {
        if (!$this->coordinates) {
            return false;
        }

        $distance = $this->coordinates->distanceTo($destination);
        return $distance <= $this->deliveryRadius;
    }

    /**
     * Get formatted delivery fee
     */
    public function getFormattedDeliveryFee(): string
    {
        return $this->deliveryFee->format();
    }

    /**
     * Get formatted minimum order
     */
    public function getFormattedMinimumOrder(): string
    {
        return $this->minimumOrder->format();
    }

    /**
     * Check if vendor has high rating
     */
    public function hasHighRating(): bool
    {
        return $this->rating >= 4.0;
    }

    /**
     * Get payment methods accepted
     */
    public function getPaymentMethods(): array
    {
        $methods = [];
        if ($this->acceptsCash) $methods[] = 'cash';
        if ($this->acceptsCard) $methods[] = 'card';
        if ($this->acceptsOnline) $methods[] = 'online';
        return $methods;
    }

    /**
     * Get cuisine types as string
     */
    public function getCuisineTypesString(): string
    {
        return implode(', ', $this->cuisineTypes);
    }

    /**
     * Get vendor URL
     */
    public function getUrl(): string
    {
        return '/restaurant/' . $this->slug;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'restaurant_name' => $this->restaurantName,
            'slug' => $this->slug,
            'email' => $this->email->getValue(),
            'phone' => $this->phone->getValue(),
            'phone_formatted' => $this->phone->format(),
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'coordinates' => $this->coordinates?->jsonSerialize(),
            'cuisine_types' => $this->cuisineTypes,
            'cuisine_types_string' => $this->getCuisineTypesString(),
            'delivery_fee' => $this->deliveryFee->getAmount(),
            'formatted_delivery_fee' => $this->deliveryFee->format(),
            'minimum_order' => $this->minimumOrder->getAmount(),
            'formatted_minimum_order' => $this->minimumOrder->format(),
            'estimated_delivery_time' => $this->estimatedDeliveryTime,
            'delivery_radius' => $this->deliveryRadius,
            'is_available' => $this->isAvailable,
            'accepts_cash' => $this->acceptsCash,
            'accepts_card' => $this->acceptsCard,
            'accepts_online' => $this->acceptsOnline,
            'payment_methods' => $this->getPaymentMethods(),
            'logo' => $this->logo,
            'cover_image' => $this->coverImage,
            'business_hours' => $this->businessHours,
            'social_links' => $this->socialLinks,
            'rating' => $this->rating,
            'reviews_count' => $this->reviewsCount,
            'has_high_rating' => $this->hasHighRating(),
            'is_currently_open' => $this->isCurrentlyOpen(),
            'url' => $this->getUrl(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'stats' => $this->stats
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
     * Parse cuisine types from string
     */
    private static function parseCuisineTypes(string $cuisineType): array
    {
        if (empty($cuisineType)) {
            return [];
        }

        // Handle JSON format
        if (str_starts_with($cuisineType, '[')) {
            $decoded = json_decode($cuisineType, true);
            return is_array($decoded) ? $decoded : [];
        }

        // Handle comma-separated format
        return array_map('trim', explode(',', $cuisineType));
    }

    /**
     * Parse business hours
     */
    private static function parseBusinessHours($businessHours): array
    {
        if (is_string($businessHours)) {
            $decoded = json_decode($businessHours, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($businessHours) ? $businessHours : [];
    }

    /**
     * Parse social links
     */
    private static function parseSocialLinks($vendor): array
    {
        return [
            'facebook' => $vendor->facebook_url ?? null,
            'instagram' => $vendor->instagram_url ?? null,
            'twitter' => $vendor->twitter_url ?? null,
            'website' => $vendor->website ?? null,
            'whatsapp' => $vendor->whatsapp_number ?? null,
        ];
    }

    /**
     * Get vendor statistics
     */
    private static function getVendorStats($vendor): array
    {
        return [
            'total_orders' => $vendor->orders_count ?? 0,
            'total_revenue' => $vendor->total_revenue ?? 0,
            'total_products' => $vendor->products_count ?? 0,
            'total_categories' => $vendor->categories_count ?? 0,
            'avg_order_value' => $vendor->avg_order_value ?? 0,
        ];
    }
}
