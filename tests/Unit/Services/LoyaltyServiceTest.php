<?php

namespace Tests\Unit\Services;

use App\Services\LoyaltyService;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyService $loyaltyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loyaltyService = new LoyaltyService();
    }

    /** @test */
    public function it_can_calculate_loyalty_points_for_order()
    {
        $customer = User::factory()->create(['type' => 3]);
        $vendor = User::factory()->create(['type' => 2]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'grand_total' => 100.00,
            'status' => 2, // Completed
        ]);

        // Assuming 1% loyalty rate
        $points = $this->loyaltyService->calculatePointsForOrder($order);

        $this->assertIsNumeric($points);
        $this->assertGreaterThan(0, $points);
    }

    /** @test */
    public function it_can_redeem_loyalty_points()
    {
        $customer = User::factory()->create(['type' => 3]);
        $initialBalance = 1000; // 1000 points

        // Set initial loyalty balance
        $this->loyaltyService->addPoints($customer->id, $initialBalance);

        $redeemAmount = 500;
        $result = $this->loyaltyService->redeemPoints($customer->id, $redeemAmount);

        $this->assertTrue($result);

        $newBalance = $this->loyaltyService->getBalance($customer->id);
        $this->assertEquals($initialBalance - $redeemAmount, $newBalance);
    }

    /** @test */
    public function it_cannot_redeem_more_points_than_available()
    {
        $customer = User::factory()->create(['type' => 3]);
        $initialBalance = 100;

        $this->loyaltyService->addPoints($customer->id, $initialBalance);

        $redeemAmount = 500; // More than available
        $result = $this->loyaltyService->redeemPoints($customer->id, $redeemAmount);

        $this->assertFalse($result);

        $balance = $this->loyaltyService->getBalance($customer->id);
        $this->assertEquals($initialBalance, $balance);
    }

    /** @test */
    public function it_can_get_customer_loyalty_history()
    {
        $customer = User::factory()->create(['type' => 3]);

        $this->loyaltyService->addPoints($customer->id, 100, 'Welcome bonus');
        $this->loyaltyService->addPoints($customer->id, 50, 'Order completion');
        $this->loyaltyService->redeemPoints($customer->id, 25);

        $history = $this->loyaltyService->getHistory($customer->id);

        $this->assertCount(3, $history);
        $this->assertEquals(125, $this->loyaltyService->getBalance($customer->id));
    }

    /** @test */
    public function it_respects_vendor_loyalty_settings()
    {
        $vendor = User::factory()->create(['type' => 2]);
        $customer = User::factory()->create(['type' => 3]);

        // Test with loyalty disabled
        $this->loyaltyService->setVendorLoyaltyStatus($vendor->id, false);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'grand_total' => 100.00,
        ]);

        $points = $this->loyaltyService->calculatePointsForOrder($order);
        $this->assertEquals(0, $points);

        // Test with loyalty enabled
        $this->loyaltyService->setVendorLoyaltyStatus($vendor->id, true);
        $points = $this->loyaltyService->calculatePointsForOrder($order);
        $this->assertGreaterThan(0, $points);
    }
}
