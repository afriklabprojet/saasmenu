<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = User::factory()->create([
            'type' => 1, // Admin
            'email' => 'admin@test.com'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function vendor_cannot_access_admin_dashboard()
    {
        $vendor = User::factory()->create([
            'type' => 2, // Vendor
            'email' => 'vendor@test.com'
        ]);

        $response = $this->actingAs($vendor)
            ->get('/admin');

        $response->assertStatus(302); // Redirect
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function admin_can_view_restaurants_list()
    {
        $admin = User::factory()->create(['type' => 1]);
        $vendor = User::factory()->create(['type' => 2]);

        Restaurant::factory()->count(3)->create([
            'vendor_id' => $vendor->id
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/restaurants');

        $response->assertStatus(200);
        $response->assertViewIs('admin.restaurants.index');
    }

    /** @test */
    public function admin_can_view_vendors_list()
    {
        $admin = User::factory()->create(['type' => 1]);

        User::factory()->count(5)->create(['type' => 2]); // Vendors

        $response = $this->actingAs($admin)
            ->get('/admin/vendors');

        $response->assertStatus(200);
        $response->assertSee('Vendors');
    }

    /** @test */
    public function admin_can_create_new_vendor()
    {
        $admin = User::factory()->create(['type' => 1]);

        $vendorData = [
            'name' => 'New Vendor',
            'email' => 'newvendor@test.com',
            'mobile' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'type' => 2,
            'is_available' => 1,
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/vendors', $vendorData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newvendor@test.com',
            'type' => 2
        ]);
    }

    /** @test */
    public function admin_can_disable_vendor()
    {
        $admin = User::factory()->create(['type' => 1]);
        $vendor = User::factory()->create(['type' => 2, 'is_available' => 1]);

        $response = $this->actingAs($admin)
            ->patch("/admin/vendors/{$vendor->id}/disable");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'is_available' => 0
        ]);
    }
}
