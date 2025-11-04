<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value Object for monetary amounts
 * Encapsulates currency and amount with validation
 */
class Money implements JsonSerializable
{
    private float $amount;
    private string $currency;

    private const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'XOF', 'XAF', 'MAD', 'TND'];

    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    /**
     * Create from string representation
     */
    public static function fromString(string $value, string $currency = 'USD'): self
    {
        $amount = floatval($value);
        return new self($amount, $currency);
    }

    /**
     * Create zero amount
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    /**
     * Add another money amount
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another money amount
     */
    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiply by a factor
     */
    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    /**
     * Divide by a factor
     */
    public function divide(float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        return new self($this->amount / $divisor, $this->currency);
    }

    /**
     * Apply percentage
     */
    public function percentage(float $percentage): self
    {
        return new self($this->amount * ($percentage / 100), $this->currency);
    }

    /**
     * Check if amount is positive
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if amount is negative
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Check if amount is zero
     */
    public function isZero(): bool
    {
        return $this->amount == 0;
    }

    /**
     * Compare with another money amount
     */
    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency &&
               abs($this->amount - $other->amount) < 0.01;
    }

    /**
     * Check if greater than another amount
     */
    public function greaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    /**
     * Check if less than another amount
     */
    public function lessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    /**
     * Get amount as float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Format for display
     */
    public function format(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'XOF' => 'CFA',
            'XAF' => 'FCFA',
            'MAD' => 'MAD',
            'TND' => 'TND'
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency;
        return $symbol . number_format($this->amount, 2);
    }

    /**
     * Convert to cents (for payment processors)
     */
    public function toCents(): int
    {
        return (int) round($this->amount * 100);
    }

    /**
     * Create from cents
     */
    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents / 100, $currency);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format()
        ];
    }

    /**
     * Validate amount
     */
    private function validateAmount(float $amount): void
    {
        if (!is_finite($amount)) {
            throw new InvalidArgumentException('Amount must be a finite number');
        }
    }

    /**
     * Validate currency
     */
    private function validateCurrency(string $currency): void
    {
        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }

        if (!in_array(strtoupper($currency), self::SUPPORTED_CURRENCIES)) {
            throw new InvalidArgumentException("Unsupported currency: {$currency}");
        }
    }

    /**
     * Ensure same currency for operations
     */
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
