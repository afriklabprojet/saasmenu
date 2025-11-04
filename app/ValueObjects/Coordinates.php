<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value Object for geographic coordinates
 * Encapsulates latitude and longitude with validation
 */
class Coordinates implements JsonSerializable
{
    private float $latitude;
    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->validateLatitude($latitude);
        $this->validateLongitude($longitude);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Create from string coordinates
     */
    public static function fromString(string $coordinates): self
    {
        $parts = explode(',', $coordinates);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid coordinates format. Expected: "lat,lng"');
        }

        $latitude = floatval(trim($parts[0]));
        $longitude = floatval(trim($parts[1]));

        return new self($latitude, $longitude);
    }

    /**
     * Get latitude
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Get longitude
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Calculate distance to another coordinate (in kilometers)
     * Using Haversine formula
     */
    public function distanceTo(Coordinates $other): float
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $latDelta = deg2rad($other->latitude - $this->latitude);
        $lngDelta = deg2rad($other->longitude - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within delivery radius
     */
    public function isWithinDeliveryRadius(Coordinates $center, float $radiusKm): bool
    {
        return $this->distanceTo($center) <= $radiusKm;
    }

    /**
     * Get formatted string for display
     */
    public function format(int $precision = 6): string
    {
        return sprintf("%.{$precision}f,%.{$precision}f", $this->latitude, $this->longitude);
    }

    /**
     * Get Google Maps URL
     */
    public function getGoogleMapsUrl(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Get what3words style representation (simplified)
     */
    public function toWhat3Words(): string
    {
        // This is a simplified example - real what3words would use their API
        $words = ['restaurant', 'delivery', 'location', 'order', 'food', 'quick', 'fresh', 'tasty'];

        $hash = md5($this->format());
        $indices = [
            hexdec(substr($hash, 0, 8)) % count($words),
            hexdec(substr($hash, 8, 8)) % count($words),
            hexdec(substr($hash, 16, 8)) % count($words),
        ];

        return implode('.', [
            $words[$indices[0]],
            $words[$indices[1]],
            $words[$indices[2]]
        ]);
    }

    /**
     * Check if coordinates are in a specific country/region
     */
    public function isInCountry(string $countryCode): bool
    {
        // Simplified country boundaries check
        switch (strtoupper($countryCode)) {
            case 'CI': // Côte d'Ivoire
                return $this->latitude >= 4.3 && $this->latitude <= 10.7 &&
                       $this->longitude >= -8.6 && $this->longitude <= -2.5;
            case 'FR': // France
                return $this->latitude >= 41.3 && $this->latitude <= 51.1 &&
                       $this->longitude >= -5.1 && $this->longitude <= 9.6;
            case 'US': // United States
                return $this->latitude >= 24.4 && $this->latitude <= 49.4 &&
                       $this->longitude >= -125.0 && $this->longitude <= -66.9;
            default:
                return false;
        }
    }

    /**
     * Get nearest major city (simplified)
     */
    public function getNearestCity(): string
    {
        // This is a simplified example - real implementation would use a cities database
        $cities = [
            ['name' => 'Abidjan', 'lat' => 5.3600, 'lng' => -4.0083],
            ['name' => 'Paris', 'lat' => 48.8566, 'lng' => 2.3522],
            ['name' => 'New York', 'lat' => 40.7128, 'lng' => -74.0060],
            ['name' => 'London', 'lat' => 51.5074, 'lng' => -0.1278],
        ];

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($cities as $city) {
            $cityCoords = new self($city['lat'], $city['lng']);
            $distance = $this->distanceTo($cityCoords);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $city['name'];
            }
        }

        return $nearest ?? 'Unknown';
    }

    /**
     * Check if equals another coordinate (with tolerance)
     */
    public function equals(Coordinates $other, float $tolerance = 0.0001): bool
    {
        return abs($this->latitude - $other->latitude) < $tolerance &&
               abs($this->longitude - $other->longitude) < $tolerance;
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'formatted' => $this->format(),
            'google_maps_url' => $this->getGoogleMapsUrl(),
            'what3words' => $this->toWhat3Words()
        ];
    }

    /**
     * Validate latitude
     */
    private function validateLatitude(float $latitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException("Invalid latitude: {$latitude}. Must be between -90 and 90");
        }
    }

    /**
     * Validate longitude
     */
    private function validateLongitude(float $longitude): void
    {
        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException("Invalid longitude: {$longitude}. Must be between -180 and 180");
        }
    }
}
