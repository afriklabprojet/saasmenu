<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LoyaltyApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $restaurant;
    protected $loyaltyProgram;
    protected $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
        $this->customer = User::factory()->create();

        $this->loyaltyProgram = LoyaltyProgram::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Programme VIP',
            'type' => 'points',
            'points_per_euro' => 10,
            'point_value' => 0.01,
            'is_active' => true
        ]);

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_loyalty_programs()
    {
        // Créer des programmes supplémentaires
        LoyaltyProgram::factory()->count(2)->create([
            'restaurant_id' => $this->restaurant->id
        ]);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/loyalty/programs");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'description',
                            'points_per_euro',
                            'point_value',
                            'is_active',
                            'members_count'
                        ]
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_create_loyalty_program()
    {
        $data = [
            'name' => 'Programme Gold',
            'description' => 'Programme pour les clients VIP',
            'type' => 'points',
            'points_per_euro' => 15,
            'point_value' => 0.02,
            'minimum_spend' => 100.00,
            'is_active' => true
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/programs", $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'type',
                        'points_per_euro',
                        'point_value'
                    ]
                ]);

        $this->assertDatabaseHas('loyalty_programs', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Programme Gold',
            'type' => 'points',
            'points_per_euro' => 15
        ]);
    }

    /** @test */
    public function it_can_enroll_customer_in_loyalty_program()
    {
        $data = [
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/members", $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'user_id',
                        'program_id',
                        'points_balance',
                        'tier_level',
                        'joined_at'
                    ]
                ]);

        $this->assertDatabaseHas('loyalty_members', [
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 0
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_enrollment()
    {
        // Inscrire d'abord le client
        LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 100
        ]);

        $data = [
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/members", $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function it_can_add_points_to_member()
    {
        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 50
        ]);

        $data = [
            'points' => 100,
            'reason' => 'Achat de 10€',
            'reference_type' => 'order',
            'reference_id' => 123
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/members/{$member->id}/points", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'points_balance' => 150,
                        'transaction' => [
                            'type' => 'credit',
                            'points' => 100,
                            'reason' => 'Achat de 10€'
                        ]
                    ]
                ]);

        $member->refresh();
        $this->assertEquals(150, $member->points_balance);

        $this->assertDatabaseHas('loyalty_transactions', [
            'member_id' => $member->id,
            'type' => 'credit',
            'points' => 100,
            'reason' => 'Achat de 10€'
        ]);
    }

    /** @test */
    public function it_can_redeem_points()
    {
        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 500
        ]);

        $data = [
            'points' => 200,
            'reason' => 'Réduction de 2€',
            'value' => 2.00
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/members/{$member->id}/redeem", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'points_balance' => 300,
                        'redemption_value' => 2.00
                    ]
                ]);

        $member->refresh();
        $this->assertEquals(300, $member->points_balance);

        $this->assertDatabaseHas('loyalty_transactions', [
            'member_id' => $member->id,
            'type' => 'debit',
            'points' => 200,
            'reason' => 'Réduction de 2€'
        ]);
    }

    /** @test */
    public function it_prevents_redeeming_more_points_than_balance()
    {
        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 50
        ]);

        $data = [
            'points' => 100,
            'reason' => 'Test redemption'
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/members/{$member->id}/redeem", $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['points']);
    }

    /** @test */
    public function it_can_get_member_loyalty_history()
    {
        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 100
        ]);

        // Créer quelques transactions
        LoyaltyTransaction::factory()->count(3)->create([
            'member_id' => $member->id,
            'program_id' => $this->loyaltyProgram->id
        ]);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/loyalty/members/{$member->id}/history");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'points',
                            'reason',
                            'created_at'
                        ]
                    ],
                    'member' => [
                        'points_balance',
                        'tier_level'
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_loyalty_analytics()
    {
        // Créer des données de test
        $members = LoyaltyMember::factory()->count(5)->create([
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 200
        ]);

        foreach ($members as $member) {
            LoyaltyTransaction::factory()->count(2)->create([
                'member_id' => $member->id,
                'program_id' => $this->loyaltyProgram->id,
                'type' => 'credit',
                'points' => 50
            ]);
        }

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/loyalty/analytics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'programs' => [
                        '*' => [
                            'id',
                            'name',
                            'members_count',
                            'total_points_issued',
                            'total_points_redeemed'
                        ]
                    ],
                    'summary' => [
                        'total_members',
                        'active_members',
                        'total_points_in_circulation',
                        'redemption_rate'
                    ]
                ]);
    }

    /** @test */
    public function it_automatically_awards_points_for_orders()
    {
        // Inscrire le client
        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 0
        ]);

        // Créer une commande
        $order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->customer->id,
            'total' => 50.00,
            'status' => 'completed'
        ]);

        // Simuler l'attribution automatique de points
        $data = [
            'order_id' => $order->id
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/process-order", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'points_awarded' => 500, // 50€ * 10 points/€
                    'member_balance' => 500
                ]);

        $member->refresh();
        $this->assertEquals(500, $member->points_balance);
    }

    /** @test */
    public function it_can_update_loyalty_program()
    {
        $data = [
            'name' => 'Programme VIP Modifié',
            'points_per_euro' => 20,
            'point_value' => 0.015
        ];

        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}/loyalty/programs/{$this->loyaltyProgram->id}", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'name' => 'Programme VIP Modifié',
                        'points_per_euro' => 20,
                        'point_value' => 0.015
                    ]
                ]);

        $this->loyaltyProgram->refresh();
        $this->assertEquals('Programme VIP Modifié', $this->loyaltyProgram->name);
        $this->assertEquals(20, $this->loyaltyProgram->points_per_euro);
    }

    /** @test */
    public function it_validates_loyalty_program_creation()
    {
        $data = [
            'name' => '', // Nom requis manquant
            'type' => 'invalid_type',
            'points_per_euro' => -5 // Valeur négative
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/loyalty/programs", $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'type', 'points_per_euro']);
    }

    /** @test */
    public function customer_can_view_their_loyalty_status()
    {
        Sanctum::actingAs($this->customer);

        $member = LoyaltyMember::create([
            'user_id' => $this->customer->id,
            'program_id' => $this->loyaltyProgram->id,
            'points_balance' => 250,
            'tier_level' => 'bronze'
        ]);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/loyalty/my-status");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'membership' => [
                        'points_balance',
                        'tier_level',
                        'program'
                    ],
                    'recent_transactions' => [],
                    'available_rewards' => []
                ]);

        $this->assertEquals(250, $response->json('membership.points_balance'));
        $this->assertEquals('bronze', $response->json('membership.tier_level'));
    }
}
