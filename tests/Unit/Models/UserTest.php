<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => bcrypt('password'),
            'type' => 1, // Admin
            'is_available' => 1,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function it_can_create_vendor_user()
    {
        $vendorData = [
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'mobile' => '0987654321',
            'password' => bcrypt('password'),
            'type' => 2, // Vendor
            'is_available' => 1,
        ];

        $vendor = User::create($vendorData);

        $this->assertEquals(2, $vendor->type);
        $this->assertTrue($vendor->type == 2); // Vendor check
    }

    /** @test */
    public function it_can_create_customer_user()
    {
        $customerData = [
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'mobile' => '1122334455',
            'password' => bcrypt('password'),
            'type' => 3, // Customer
            'is_available' => 1,
        ];

        $customer = User::create($customerData);

        $this->assertEquals(3, $customer->type);
        $this->assertTrue($customer->type == 3); // Customer check
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        User::create([
            'name' => 'First User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => bcrypt('password'),
            'type' => 1,
            'is_available' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'Second User',
            'email' => 'test@example.com',
            'mobile' => '0987654321',
            'password' => bcrypt('password'),
            'type' => 1,
            'is_available' => 1,
        ]);
    }

    /** @test */
    public function user_can_be_enabled_or_disabled()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => bcrypt('password'),
            'type' => 1,
            'is_available' => 1,
        ]);

        $this->assertEquals(1, $user->is_available);

        $user->update(['is_available' => 0]);

        $this->assertEquals(0, $user->fresh()->is_available);
    }
}
