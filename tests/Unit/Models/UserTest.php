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

        $user = User::forceCreate($userData);

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

        ];

        $vendor = User::forceCreate($vendorData);

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

        ];

        $customer = User::forceCreate($customerData);

        $this->assertEquals(3, $customer->type);
        $this->assertTrue($customer->type == 3); // Customer check
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        User::forceCreate([
            'name' => 'First User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => bcrypt('password'),
            'type' => 1,

        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::forceCreate([
            'name' => 'Second User',
            'email' => 'test@example.com',
            'mobile' => '0987654321',
            'password' => bcrypt('password'),
            'type' => 1,

        ]);
    }

    /** @test */
    public function user_can_be_verified()
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => bcrypt('password'),
            'type' => 1,
            'is_verified' => 0,
        ]);

        $this->assertEquals(0, $user->is_verified);

        $user->forceFill(['is_verified' => 1])->save();

        $this->assertEquals(1, $user->fresh()->is_verified);
    }
}
