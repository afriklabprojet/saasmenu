<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value Object for phone numbers
 * Encapsulates phone validation and formatting
 */
class PhoneNumber implements JsonSerializable
{
    private string $value;
    private string $countryCode;
    private string $nationalNumber;

    public function __construct(string $phone, string $defaultCountryCode = '+1')
    {
        $this->value = $this->normalize($phone);
        $this->validate($this->value);
        $this->parseNumber($this->value, $defaultCountryCode);
    }

    /**
     * Create from string
     */
    public static function fromString(string $phone, string $defaultCountryCode = '+1'): self
    {
        return new self($phone, $defaultCountryCode);
    }

    /**
     * Get full phone number
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get country code
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Get national number
     */
    public function getNationalNumber(): string
    {
        return $this->nationalNumber;
    }

    /**
     * Format for display
     */
    public function format(): string
    {
        // Format based on country code
        switch ($this->countryCode) {
            case '+1': // US/Canada
                if (strlen($this->nationalNumber) === 10) {
                    return sprintf('(%s) %s-%s',
                        substr($this->nationalNumber, 0, 3),
                        substr($this->nationalNumber, 3, 3),
                        substr($this->nationalNumber, 6, 4)
                    );
                }
                break;
            case '+33': // France
                if (strlen($this->nationalNumber) === 9) {
                    return sprintf('%s %s %s %s %s',
                        substr($this->nationalNumber, 0, 1),
                        substr($this->nationalNumber, 1, 2),
                        substr($this->nationalNumber, 3, 2),
                        substr($this->nationalNumber, 5, 2),
                        substr($this->nationalNumber, 7, 2)
                    );
                }
                break;
            case '+225': // Côte d'Ivoire
                if (strlen($this->nationalNumber) === 8) {
                    return sprintf('%s %s %s %s',
                        substr($this->nationalNumber, 0, 2),
                        substr($this->nationalNumber, 2, 2),
                        substr($this->nationalNumber, 4, 2),
                        substr($this->nationalNumber, 6, 2)
                    );
                }
                break;
        }

        // Default format
        return $this->countryCode . ' ' . $this->nationalNumber;
    }

    /**
     * Format for international dialing
     */
    public function formatInternational(): string
    {
        return $this->countryCode . $this->nationalNumber;
    }

    /**
     * Check if mobile number
     */
    public function isMobile(): bool
    {
        switch ($this->countryCode) {
            case '+1': // US/Canada
                return in_array(substr($this->nationalNumber, 0, 3), [
                    '201', '202', '203', '205', '206', '207', '208', '209', '210'
                    // Simplified list - in real app, use a comprehensive mobile prefix list
                ]);
            case '+33': // France
                return in_array(substr($this->nationalNumber, 0, 1), ['6', '7']);
            case '+225': // Côte d'Ivoire
                return in_array(substr($this->nationalNumber, 0, 2), ['01', '02', '03', '05', '07']);
        }

        return false; // Unknown, assume not mobile
    }

    /**
     * Check if landline number
     */
    public function isLandline(): bool
    {
        return !$this->isMobile();
    }

    /**
     * Check if WhatsApp compatible
     */
    public function isWhatsAppCompatible(): bool
    {
        // Most mobile numbers support WhatsApp
        return $this->isMobile() && strlen($this->nationalNumber) >= 7;
    }

    /**
     * Obfuscate phone for display (privacy)
     */
    public function obfuscate(): string
    {
        $formatted = $this->format();
        $length = strlen($formatted);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $visible = 2;
        $hidden = $length - $visible * 2;

        return substr($formatted, 0, $visible) .
               str_repeat('*', $hidden) .
               substr($formatted, -$visible);
    }

    /**
     * Check if equals another phone
     */
    public function equals(PhoneNumber $other): bool
    {
        return $this->value === $other->value;
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
            'value' => $this->value,
            'country_code' => $this->countryCode,
            'national_number' => $this->nationalNumber,
            'formatted' => $this->format(),
            'international' => $this->formatInternational(),
            'is_mobile' => $this->isMobile()
        ];
    }

    /**
     * Normalize phone number
     */
    private function normalize(string $phone): string
    {
        // Remove all non-digit characters except +
        $normalized = preg_replace('/[^\d\+]/', '', trim($phone));

        if (empty($normalized)) {
            throw new InvalidArgumentException('Phone number cannot be empty');
        }

        return $normalized;
    }

    /**
     * Validate phone number
     */
    private function validate(string $phone): void
    {
        if (empty($phone)) {
            throw new InvalidArgumentException('Phone number cannot be empty');
        }

        if (!preg_match('/^\+?[\d]{7,15}$/', $phone)) {
            throw new InvalidArgumentException("Invalid phone number format: {$phone}");
        }

        if (strlen($phone) > 16) {
            throw new InvalidArgumentException('Phone number too long');
        }
    }

    /**
     * Parse phone number into components
     */
    private function parseNumber(string $phone, string $defaultCountryCode): void
    {
        if (strpos($phone, '+') === 0) {
            // Has country code
            $this->parseWithCountryCode($phone);
        } else {
            // No country code, use default
            $this->countryCode = $defaultCountryCode;
            $this->nationalNumber = $phone;
            $this->value = $this->countryCode . $this->nationalNumber;
        }
    }

    /**
     * Parse phone with country code
     */
    private function parseWithCountryCode(string $phone): void
    {
        // Common country codes (simplified)
        $countryCodes = ['+1', '+33', '+44', '+49', '+86', '+225', '+221', '+226', '+227', '+228'];

        foreach ($countryCodes as $code) {
            if (strpos($phone, $code) === 0) {
                $this->countryCode = $code;
                $this->nationalNumber = substr($phone, strlen($code));
                return;
            }
        }

        // Default: assume first 1-3 digits are country code
        if (preg_match('/^\+(\d{1,3})(\d+)$/', $phone, $matches)) {
            $this->countryCode = '+' . $matches[1];
            $this->nationalNumber = $matches[2];
        } else {
            throw new InvalidArgumentException("Unable to parse country code from: {$phone}");
        }
    }
}
