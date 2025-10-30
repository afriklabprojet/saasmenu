<?php

namespace Tests\Unit\Services;

use App\Services\PaymentService;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    /** @test */
    public function it_can_instantiate_payment_service()
    {
        $this->assertInstanceOf(PaymentService::class, $this->paymentService);
    }

    /** @test */
    public function it_can_calculate_tax()
    {
        $amount = 100.00;
        $taxRate = 0.08; // 8%

        $tax = $amount * $taxRate;

        $this->assertEquals(8.00, $tax);
    }

    /** @test */
    public function it_can_calculate_total_with_tax_and_delivery()
    {
        $subtotal = 50.00;
        $tax = 4.00;
        $deliveryCharge = 5.99;
        $discount = 10.00;

        $total = $subtotal + $tax + $deliveryCharge - $discount;

        $this->assertEquals(49.99, $total);
    }

    /** @test */
    public function it_validates_payment_amounts()
    {
        $validAmount = 25.50;
        $invalidAmount = -10.00;

        $this->assertGreaterThan(0, $validAmount);
        $this->assertLessThan(0, $invalidAmount);
    }

    /** @test */
    public function it_can_format_currency()
    {
        $amount = 123.456;
        $formatted = number_format($amount, 2);

        $this->assertEquals('123.46', $formatted);
    }
}
