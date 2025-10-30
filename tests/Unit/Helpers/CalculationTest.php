<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class CalculationTest extends TestCase
{
    /** @test */
    public function it_can_calculate_percentage()
    {
        $amount = 100;
        $percentage = 15;

        $result = ($amount * $percentage) / 100;

        $this->assertEquals(15, $result);
    }

    /** @test */
    public function it_can_calculate_tax_amount()
    {
        $subtotal = 50.00;
        $taxRate = 8.5; // 8.5%

        $taxAmount = ($subtotal * $taxRate) / 100;

        $this->assertEquals(4.25, $taxAmount);
    }

    /** @test */
    public function it_can_calculate_discount()
    {
        $originalPrice = 100.00;
        $discountPercent = 20;

        $discountAmount = ($originalPrice * $discountPercent) / 100;
        $finalPrice = $originalPrice - $discountAmount;

        $this->assertEquals(20.00, $discountAmount);
        $this->assertEquals(80.00, $finalPrice);
    }

    /** @test */
    public function it_can_calculate_delivery_charge_by_distance()
    {
        $baseCharge = 3.00;
        $perKmCharge = 1.50;
        $distance = 5; // km

        $totalDeliveryCharge = $baseCharge + ($perKmCharge * $distance);

        $this->assertEquals(10.50, $totalDeliveryCharge);
    }

    /** @test */
    public function it_can_format_currency()
    {
        $amount = 1234.567;
        $formatted = number_format($amount, 2);

        $this->assertEquals('1,234.57', $formatted);
    }

    /** @test */
    public function it_validates_phone_number_format()
    {
        $validPhone = '1234567890';
        $invalidPhone = '123';

        $this->assertTrue(strlen($validPhone) >= 10);
        $this->assertFalse(strlen($invalidPhone) >= 10);
    }

    /** @test */
    public function it_can_calculate_time_difference()
    {
        $startTime = '09:00';
        $endTime = '17:30';

        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $diffHours = ($end - $start) / 3600;

        $this->assertEquals(8.5, $diffHours);
    }
}
