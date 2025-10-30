<?php

namespace Tests\Unit\Services;

use App\Services\LoyaltyService;
use PHPUnit\Framework\TestCase;

class LoyaltyServiceTest extends TestCase
{
    private LoyaltyService $loyaltyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loyaltyService = new LoyaltyService();
    }

    /** @test */
    public function it_can_instantiate_loyalty_service()
    {
        $this->assertInstanceOf(LoyaltyService::class, $this->loyaltyService);
    }

    /** @test */
    public function it_can_calculate_points_from_amount()
    {
        // Create a mock restaurant object with required properties
        $restaurant = new \stdClass();
        $restaurant->loyalty_program_active = true;
        $restaurant->loyalty_points_per_euro = 1.5; // 1.5 points per euro
        
        $amount = 100.00;
        $points = $this->loyaltyService->calculatePointsFromAmount($amount, $restaurant);

        $this->assertIsNumeric($points);
        $this->assertEquals(150, $points); // 100 * 1.5 = 150 points (floor)
    }

    /** @test */
    public function it_returns_zero_when_loyalty_program_inactive()
    {
        $restaurant = new \stdClass();
        $restaurant->loyalty_program_active = false;
        $restaurant->loyalty_points_per_euro = 1.0;
        
        $amount = 100.00;
        $points = $this->loyaltyService->calculatePointsFromAmount($amount, $restaurant);

        $this->assertEquals(0, $points);
    }

    /** @test */
    public function it_can_calculate_points_from_different_amounts()
    {
        $restaurant = new \stdClass();
        $restaurant->loyalty_program_active = true;
        $restaurant->loyalty_points_per_euro = 2.0; // 2 points per euro
        
        // Test different amounts
        $testCases = [
            ['amount' => 50.00, 'expected' => 100], // 50 * 2 = 100
            ['amount' => 25.00, 'expected' => 50],  // 25 * 2 = 50
            ['amount' => 10.50, 'expected' => 21],  // 10.5 * 2 = 21 (floor)
        ];

        foreach ($testCases as $testCase) {
            $points = $this->loyaltyService->calculatePointsFromAmount($testCase['amount'], $restaurant);
            $this->assertEquals($testCase['expected'], $points, 
                "Failed for amount: {$testCase['amount']}"
            );
        }
    }

    /** @test */
    public function it_handles_zero_amount()
    {
        $restaurant = new \stdClass();
        $restaurant->loyalty_program_active = true;
        $restaurant->loyalty_points_per_euro = 1.0;
        
        $points = $this->loyaltyService->calculatePointsFromAmount(0, $restaurant);
        
        $this->assertEquals(0, $points);
    }

    /** @test */
    public function it_uses_default_points_per_euro_when_not_set()
    {
        $restaurant = new \stdClass();
        $restaurant->loyalty_program_active = true;
        // No loyalty_points_per_euro set - should use default of 1
        
        $amount = 100.00;
        $points = $this->loyaltyService->calculatePointsFromAmount($amount, $restaurant);
        
        $this->assertEquals(100, $points); // 100 * 1 (default) = 100
    }
}