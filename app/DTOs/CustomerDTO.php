<?php

namespace App\DTOs;

use App\ValueObjects\Email;
use App\ValueObjects\PhoneNumber;
use JsonSerializable;

/**
 * DTO for Customer data transfer
 */
class CustomerDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly Email $email,
        public readonly PhoneNumber $phone,
        public readonly ?string $dateOfBirth,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $postalCode,
        public readonly ?string $country,
        public readonly bool $isActive,
        public readonly ?string $lastLoginAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly int $totalOrders = 0,
        public readonly float $totalSpent = 0.0,
        public readonly ?string $preferredLanguage = null
    ) {}

    /**
     * Create from Eloquent model
     */
    public static function fromModel($customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name ?? '',
            email: Email::fromString($customer->email ?? ''),
            phone: PhoneNumber::fromString($customer->mobile ?? $customer->phone ?? ''),
            dateOfBirth: $customer->date_of_birth,
            address: $customer->address,
            city: $customer->city,
            postalCode: $customer->postal_code,
            country: $customer->country,
            isActive: $customer->is_active ?? true,
            lastLoginAt: $customer->last_login_at?->toISOString(),
            createdAt: $customer->created_at->toISOString(),
            updatedAt: $customer->updated_at->toISOString(),
            totalOrders: $customer->orders_count ?? 0,
            totalSpent: floatval($customer->total_spent ?? 0),
            preferredLanguage: $customer->preferred_language ?? 'fr'
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
            email: Email::fromString($data['email'] ?? ''),
            phone: PhoneNumber::fromString($data['phone'] ?? ''),
            dateOfBirth: $data['date_of_birth'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            isActive: $data['is_active'] ?? true,
            lastLoginAt: $data['last_login_at'] ?? null,
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            totalOrders: $data['total_orders'] ?? 0,
            totalSpent: floatval($data['total_spent'] ?? 0),
            preferredLanguage: $data['preferred_language'] ?? 'fr'
        );
    }

    /**
     * Get customer's age
     */
    public function getAge(): ?int
    {
        if (!$this->dateOfBirth) {
            return null;
        }

        $birthDate = new \DateTime($this->dateOfBirth);
        $today = new \DateTime();

        return $today->diff($birthDate)->y;
    }

    /**
     * Check if customer is VIP (based on total spent)
     */
    public function isVip(): bool
    {
        return $this->totalSpent >= 1000; // VIP threshold
    }

    /**
     * Check if customer is frequent (based on order count)
     */
    public function isFrequentCustomer(): bool
    {
        return $this->totalOrders >= 10;
    }

    /**
     * Get customer loyalty tier
     */
    public function getLoyaltyTier(): string
    {
        if ($this->totalSpent >= 5000) return 'Platinum';
        if ($this->totalSpent >= 2000) return 'Gold';
        if ($this->totalSpent >= 500) return 'Silver';
        return 'Bronze';
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue(): float
    {
        if ($this->totalOrders === 0) {
            return 0;
        }

        return $this->totalSpent / $this->totalOrders;
    }

    /**
     * Check if customer is active recently
     */
    public function isActiveRecently(): bool
    {
        if (!$this->lastLoginAt) {
            return false;
        }

        $lastLogin = new \DateTime($this->lastLoginAt);
        $thirtyDaysAgo = new \DateTime('-30 days');

        return $lastLogin > $thirtyDaysAgo;
    }

    /**
     * Get full address
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postalCode,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone->getValue(),
            'phone_formatted' => $this->phone->format(),
            'date_of_birth' => $this->dateOfBirth,
            'age' => $this->getAge(),
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'full_address' => $this->getFullAddress(),
            'is_active' => $this->isActive,
            'last_login_at' => $this->lastLoginAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'total_orders' => $this->totalOrders,
            'total_spent' => $this->totalSpent,
            'average_order_value' => $this->getAverageOrderValue(),
            'preferred_language' => $this->preferredLanguage,
            'is_vip' => $this->isVip(),
            'is_frequent_customer' => $this->isFrequentCustomer(),
            'loyalty_tier' => $this->getLoyaltyTier(),
            'is_active_recently' => $this->isActiveRecently()
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
