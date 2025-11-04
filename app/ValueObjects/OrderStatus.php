<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value Object for order status
 * Encapsulates status validation and transitions
 */
class OrderStatus implements JsonSerializable
{
    private string $value;

    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PREPARING = 'preparing';
    public const READY = 'ready';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const REFUNDED = 'refunded';

    private const VALID_STATUSES = [
        self::PENDING,
        self::CONFIRMED,
        self::PREPARING,
        self::READY,
        self::OUT_FOR_DELIVERY,
        self::DELIVERED,
        self::COMPLETED,
        self::CANCELLED,
        self::REFUNDED,
    ];

    private const STATUS_TRANSITIONS = [
        self::PENDING => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::PREPARING, self::CANCELLED],
        self::PREPARING => [self::READY, self::CANCELLED],
        self::READY => [self::OUT_FOR_DELIVERY, self::COMPLETED, self::CANCELLED],
        self::OUT_FOR_DELIVERY => [self::DELIVERED, self::CANCELLED],
        self::DELIVERED => [self::COMPLETED],
        self::COMPLETED => [self::REFUNDED],
        self::CANCELLED => [],
        self::REFUNDED => [],
    ];

    private const STATUS_LABELS = [
        self::PENDING => 'En attente',
        self::CONFIRMED => 'Confirmée',
        self::PREPARING => 'En préparation',
        self::READY => 'Prête',
        self::OUT_FOR_DELIVERY => 'En livraison',
        self::DELIVERED => 'Livrée',
        self::COMPLETED => 'Terminée',
        self::CANCELLED => 'Annulée',
        self::REFUNDED => 'Remboursée',
    ];

    private const STATUS_COLORS = [
        self::PENDING => '#fbbf24',
        self::CONFIRMED => '#3b82f6',
        self::PREPARING => '#f59e0b',
        self::READY => '#10b981',
        self::OUT_FOR_DELIVERY => '#8b5cf6',
        self::DELIVERED => '#059669',
        self::COMPLETED => '#16a34a',
        self::CANCELLED => '#ef4444',
        self::REFUNDED => '#6b7280',
    ];

    public function __construct(string $status)
    {
        $this->validate($status);
        $this->value = $status;
    }

    /**
     * Create from string
     */
    public static function fromString(string $status): self
    {
        return new self($status);
    }

    /**
     * Create pending status
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * Create confirmed status
     */
    public static function confirmed(): self
    {
        return new self(self::CONFIRMED);
    }

    /**
     * Create cancelled status
     */
    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    /**
     * Get status value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get human-readable label
     */
    public function getLabel(): string
    {
        return self::STATUS_LABELS[$this->value];
    }

    /**
     * Get status color
     */
    public function getColor(): string
    {
        return self::STATUS_COLORS[$this->value];
    }

    /**
     * Check if status is pending
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Check if status is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    /**
     * Check if status is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    /**
     * Check if status is completed
     */
    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    /**
     * Check if status is final (no more transitions possible)
     */
    public function isFinal(): bool
    {
        return in_array($this->value, [self::COMPLETED, self::CANCELLED, self::REFUNDED]);
    }

    /**
     * Check if status is active (order in progress)
     */
    public function isActive(): bool
    {
        return in_array($this->value, [
            self::CONFIRMED,
            self::PREPARING,
            self::READY,
            self::OUT_FOR_DELIVERY
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array(self::CANCELLED, $this->getAllowedTransitions());
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->value === self::COMPLETED;
    }

    /**
     * Get allowed status transitions
     */
    public function getAllowedTransitions(): array
    {
        return self::STATUS_TRANSITIONS[$this->value] ?? [];
    }

    /**
     * Check if transition to another status is allowed
     */
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus->getValue(), $this->getAllowedTransitions());
    }

    /**
     * Transition to a new status
     */
    public function transitionTo(string $newStatus): self
    {
        $newStatusObj = new self($newStatus);

        if (!$this->canTransitionTo($newStatusObj)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$this->value}' to '{$newStatus}'"
            );
        }

        return $newStatusObj;
    }

    /**
     * Get next possible statuses
     */
    public function getNextStatuses(): array
    {
        return array_map(
            fn($status) => new self($status),
            $this->getAllowedTransitions()
        );
    }

    /**
     * Get all valid statuses
     */
    public static function getAllStatuses(): array
    {
        return array_map(
            fn($status) => new self($status),
            self::VALID_STATUSES
        );
    }

    /**
     * Check if equals another status
     */
    public function equals(OrderStatus $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->getLabel();
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'is_final' => $this->isFinal(),
            'is_active' => $this->isActive(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'allowed_transitions' => $this->getAllowedTransitions()
        ];
    }

    /**
     * Validate status
     */
    private function validate(string $status): void
    {
        if (empty($status)) {
            throw new InvalidArgumentException('Status cannot be empty');
        }

        if (!in_array($status, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid status: {$status}");
        }
    }
}
