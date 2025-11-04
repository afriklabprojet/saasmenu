<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value Object for email addresses
 * Encapsulates email validation and normalization
 */
class Email implements JsonSerializable
{
    private string $value;

    public function __construct(string $email)
    {
        $this->validate($email);
        $this->value = $this->normalize($email);
    }

    /**
     * Create from string
     */
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    /**
     * Get email value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get domain part
     */
    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    /**
     * Get local part (before @)
     */
    public function getLocalPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    /**
     * Check if email is from a specific domain
     */
    public function isFromDomain(string $domain): bool
    {
        return strtolower($this->getDomain()) === strtolower($domain);
    }

    /**
     * Check if email is from a business domain (not free email providers)
     */
    public function isBusinessEmail(): bool
    {
        $freeProviders = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'icloud.com', 'aol.com', 'live.com', 'msn.com',
            'ymail.com', 'mail.com', 'protonmail.com'
        ];

        return !in_array(strtolower($this->getDomain()), $freeProviders);
    }

    /**
     * Obfuscate email for display (privacy)
     */
    public function obfuscate(): string
    {
        $parts = explode('@', $this->value);
        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 2) {
            $obfuscatedLocal = str_repeat('*', strlen($local));
        } else {
            $obfuscatedLocal = substr($local, 0, 2) . str_repeat('*', strlen($local) - 2);
        }

        return $obfuscatedLocal . '@' . $domain;
    }

    /**
     * Check if equals another email
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * Validate email format
     */
    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }

        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email too long (max 254 characters)');
        }

        // Check for dangerous characters
        if (preg_match('/[<>"\']/', $email)) {
            throw new InvalidArgumentException('Email contains dangerous characters');
        }
    }

    /**
     * Normalize email (lowercase, trim)
     */
    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
