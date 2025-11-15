<?php

namespace Tests\Feature\Admin\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user and authenticate
        $this->adminUser = User::factory()->create([
            'type' => 2, // Vendor type
            'email' => 'admin@test.com'
        ]);

        Sanctum::actingAs($this->adminUser);
    }

    /** @test */
    public function can_list_notifications()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->getJson('/api/admin/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'type', 'title', 'message', 'read_at', 'priority']
                ]
            ]);
    }

    /** @test */
    public function can_filter_notifications_by_user_id()
    {
        $user1 = User::factory()->create(['type' => 2]);
        $user2 = User::factory()->create(['type' => 2]);

        Notification::factory()->count(2)->create(['user_id' => $user1->id]);
        Notification::factory()->count(3)->create(['user_id' => $user2->id]);

        $response = $this->getJson('/api/admin/notifications?user_id=' . $user1->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_notifications_by_type()
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->adminUser->id,
            'type' => 'order'
        ]);
        Notification::factory()->count(3)->create([
            'user_id' => $this->adminUser->id,
            'type' => 'payment'
        ]);

        $response = $this->getJson('/api/admin/notifications?type=order');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_unread_notifications()
    {
        Notification::factory()->count(2)->unread()->create([
            'user_id' => $this->adminUser->id
        ]);
        Notification::factory()->count(3)->read()->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->getJson('/api/admin/notifications?read=false');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_filter_by_priority()
    {
        Notification::factory()->count(2)->create([
            'user_id' => $this->adminUser->id,
            'priority' => 'high'
        ]);
        Notification::factory()->count(1)->create([
            'user_id' => $this->adminUser->id,
            'priority' => 'low'
        ]);

        $response = $this->getJson('/api/admin/notifications?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_create_notification()
    {
        $notificationData = [
            'user_id' => $this->adminUser->id,
            'type' => 'system',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'priority' => 'medium',
            'action_url' => 'https://example.com',
            'data' => ['key' => 'value']
        ];

        $response = $this->postJson('/api/admin/notifications', $notificationData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Notification created successfully',
                'notification' => [
                    'type' => 'system',
                    'title' => 'Test Notification',
                    'priority' => 'medium'
                ]
            ]);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Test Notification',
            'type' => 'system'
        ]);
    }

    /** @test */
    public function create_notification_requires_title()
    {
        $response = $this->postJson('/api/admin/notifications', [
            'type' => 'system',
            'message' => 'Test message'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function can_show_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->adminUser->id,
            'title' => 'Show Test'
        ]);

        $response = $this->getJson('/api/admin/notifications/' . $notification->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $notification->id,
                'title' => 'Show Test'
            ]);
    }

    /** @test */
    public function can_mark_notification_as_read()
    {
        $notification = Notification::factory()->unread()->create([
            'user_id' => $this->adminUser->id
        ]);

        $this->assertNull($notification->read_at);

        $response = $this->patchJson('/api/admin/notifications/' . $notification->id . '/read');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification marked as read'
            ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function can_mark_all_notifications_as_read_for_user()
    {
        Notification::factory()->count(3)->unread()->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->postJson('/api/admin/notifications/mark-all-read', [
            'user_id' => $this->adminUser->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All notifications marked as read',
                'count' => 3
            ]);

        $unreadCount = Notification::where('user_id', $this->adminUser->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /** @test */
    public function can_get_unread_notifications_count()
    {
        Notification::factory()->count(5)->unread()->create([
            'user_id' => $this->adminUser->id
        ]);
        Notification::factory()->count(2)->read()->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->getJson('/api/admin/notifications/unread/count?user_id=' . $this->adminUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'unread_count' => 5
            ]);
    }

    /** @test */
    public function can_delete_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->deleteJson('/api/admin/notifications/' . $notification->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification deleted successfully'
            ]);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    /** @test */
    public function can_paginate_notifications()
    {
        Notification::factory()->count(25)->create([
            'user_id' => $this->adminUser->id
        ]);

        $response = $this->getJson('/api/admin/notifications?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }
}
