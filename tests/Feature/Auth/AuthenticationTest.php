<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'type' => 3 // Customer
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function admin_can_login_to_admin_panel()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 1 // Admin
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /** @test */
    public function vendor_can_login_to_admin_panel()
    {
        $vendor = User::factory()->create([
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'type' => 2 // Vendor
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'vendor@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($vendor, 'admin');
    }

    /** @test */
    public function customer_cannot_login_to_admin_panel()
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'type' => 3 // Customer
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('admin');
    }

    /** @test */
    public function user_can_register_as_customer()
    {
        $userData = [
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'mobile' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@example.com',
            'type' => 3 // Customer
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'mobile' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'mobile' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function admin_can_logout_from_admin_panel()
    {
        $admin = User::factory()->create(['type' => 1]);

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }
}
