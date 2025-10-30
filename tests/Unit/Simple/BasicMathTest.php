<?php

namespace Tests\Unit\Simple;

use PHPUnit\Framework\TestCase;

class BasicMathTest extends TestCase
{
    /**
     * Test de calculs simples
     */
    public function test_can_calculate_percentage(): void
    {
        $percentage = (100 * 15) / 100;
        $this->assertEquals(15, $percentage);
    }

    /**
     * Test de calcul de taxe
     */
    public function test_can_calculate_tax(): void
    {
        $amount = 100;
        $taxRate = 10; // 10%
        $tax = ($amount * $taxRate) / 100;

        $this->assertEquals(10, $tax);
    }

    /**
     * Test de formatage de devise
     */
    public function test_can_format_currency(): void
    {
        $amount = 123.45;
        $formatted = number_format($amount, 2, '.', ',');

        $this->assertEquals('123.45', $formatted);
    }

    /**
     * Test de validation d'email
     */
    public function test_can_validate_email(): void
    {
        $validEmail = 'test@example.com';
        $invalidEmail = 'invalid-email';

        $this->assertTrue(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) !== false);
    }

    /**
     * Test de calcul de remise
     */
    public function test_can_calculate_discount(): void
    {
        $originalPrice = 100;
        $discountPercent = 20;
        $discountAmount = ($originalPrice * $discountPercent) / 100;
        $finalPrice = $originalPrice - $discountAmount;

        $this->assertEquals(20, $discountAmount);
        $this->assertEquals(80, $finalPrice);
    }

    /**
     * Test de génération de code de commande
     */
    public function test_can_generate_order_code(): void
    {
        $prefix = 'ORD';
        $timestamp = time();
        $orderCode = $prefix . '-' . $timestamp;

        $this->assertStringStartsWith('ORD-', $orderCode);
        $this->assertGreaterThan(10, strlen($orderCode));
    }

    /**
     * Test de validation de numéro de téléphone
     */
    public function test_can_validate_phone_number(): void
    {
        $validPhones = ['+1234567890', '0123456789', '+33123456789'];
        $invalidPhones = ['123', 'abc123', ''];

        foreach ($validPhones as $phone) {
            $isValid = preg_match('/^[\+]?[\d\s\-\(\)]{10,}$/', $phone);
            $this->assertTrue($isValid > 0, "Le téléphone {$phone} devrait être valide");
        }

        foreach ($invalidPhones as $phone) {
            $isValid = preg_match('/^[\+]?[\d\s\-\(\)]{10,}$/', $phone);
            $this->assertFalse($isValid > 0, "Le téléphone {$phone} devrait être invalide");
        }
    }

    /**
     * Test de calcul de temps de livraison
     */
    public function test_can_calculate_delivery_time(): void
    {
        $baseTime = 30; // minutes
        $distance = 5; // km
        $additionalTimePerKm = 5; // minutes

        $totalTime = $baseTime + ($distance * $additionalTimePerKm);

        $this->assertEquals(55, $totalTime);
    }
}
