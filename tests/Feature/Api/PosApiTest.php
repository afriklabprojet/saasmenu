<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\POSSession;
use App\Models\POSTerminal;
use App\Models\POSCart;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PosApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $restaurant;
    protected $terminal;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
        $this->terminal = POSTerminal::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'active'
        ]);

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_pos_sessions()
    {
        // Créer quelques sessions
        POSSession::factory()->count(3)->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/pos/sessions");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'terminal_id',
                            'user_id',
                            'status',
                            'started_at',
                            'total_sales'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'per_page',
                        'current_page'
                    ]
                ]);

        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_start_pos_session()
    {
        $data = [
            'terminal_id' => $this->terminal->id,
            'opening_cash' => 100.00
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/sessions", $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'terminal_id',
                        'user_id',
                        'status',
                        'opening_cash',
                        'started_at'
                    ]
                ]);

        $this->assertDatabaseHas('pos_sessions', [
            'restaurant_id' => $this->restaurant->id,
            'terminal_id' => $this->terminal->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'opening_cash' => 100.00
        ]);
    }

    /** @test */
    public function it_prevents_multiple_active_sessions_per_terminal()
    {
        // Créer une session active
        POSSession::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'terminal_id' => $this->terminal->id,
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $data = [
            'terminal_id' => $this->terminal->id,
            'opening_cash' => 50.00
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/sessions", $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['terminal_id']);
    }

    /** @test */
    public function it_can_close_pos_session()
    {
        $session = POSSession::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'terminal_id' => $this->terminal->id,
            'user_id' => $this->user->id,
            'status' => 'active',
            'opening_cash' => 100.00
        ]);

        $data = [
            'closing_cash' => 150.00,
            'notes' => 'Session fermée normalement'
        ];

        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}/pos/sessions/{$session->id}/close", $data);

        $response->assertStatus(200);

        $session->refresh();
        $this->assertEquals('closed', $session->status);
        $this->assertEquals(150.00, $session->closing_cash);
        $this->assertNotNull($session->ended_at);
    }

    /** @test */
    public function it_can_create_pos_cart()
    {
        $session = POSSession::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'price' => 15.99
        ]);

        $data = [
            'session_id' => $session->id,
            'items' => [
                [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => 2,
                    'unit_price' => 15.99,
                    'modifications' => []
                ]
            ]
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/carts", $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'session_id',
                        'items',
                        'subtotal',
                        'total',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('pos_carts', [
            'session_id' => $session->id,
            'subtotal' => 31.98, // 15.99 * 2
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_update_cart_items()
    {
        $cart = POSCart::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'active'
        ]);

        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'price' => 12.50
        ]);

        $data = [
            'items' => [
                [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => 3,
                    'unit_price' => 12.50,
                    'modifications' => ['extra_cheese']
                ]
            ]
        ];

        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}/pos/carts/{$cart->id}", $data);

        $response->assertStatus(200);

        $cart->refresh();
        $this->assertEquals(37.50, $cart->subtotal); // 12.50 * 3
    }

    /** @test */
    public function it_can_checkout_cart_to_create_order()
    {
        $session = POSSession::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $cart = POSCart::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'session_id' => $session->id,
            'subtotal' => 25.00,
            'total' => 27.50,
            'status' => 'active'
        ]);

        $data = [
            'payment_method' => 'cash',
            'customer_id' => null,
            'notes' => 'Commande sur place'
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/carts/{$cart->id}/checkout", $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'order_id',
                        'receipt'
                    ]
                ]);

        // Vérifier que la commande a été créée
        $this->assertDatabaseHas('orders', [
            'restaurant_id' => $this->restaurant->id,
            'total' => 27.50,
            'payment_method' => 'cash',
            'source' => 'pos'
        ]);

        // Vérifier que le panier est marqué comme terminé
        $cart->refresh();
        $this->assertEquals('completed', $cart->status);
    }

    /** @test */
    public function it_can_get_pos_analytics()
    {
        // Créer quelques données de test
        POSSession::factory()->count(2)->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'closed',
            'total_sales' => 150.00,
            'started_at' => now()->subHours(5),
            'ended_at' => now()->subHours(2)
        ]);

        Order::factory()->count(5)->create([
            'restaurant_id' => $this->restaurant->id,
            'source' => 'pos',
            'total' => 30.00,
            'created_at' => now()->subHours(3)
        ]);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/pos/analytics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'today' => [
                        'sales',
                        'orders',
                        'average_order_value'
                    ],
                    'sessions' => [
                        'total',
                        'active',
                        'closed'
                    ],
                    'popular_items' => [],
                    'hourly_sales' => []
                ]);
    }

    /** @test */
    public function it_validates_required_fields_for_session_creation()
    {
        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/sessions", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['terminal_id', 'opening_cash']);
    }

    /** @test */
    public function it_prevents_access_to_other_restaurant_pos_data()
    {
        $otherRestaurant = Restaurant::factory()->create();
        $otherSession = POSSession::factory()->create([
            'restaurant_id' => $otherRestaurant->id
        ]);

        $response = $this->getJson("/api/restaurants/{$otherRestaurant->id}/pos/sessions");

        $response->assertStatus(403); // Accès refusé par le middleware
    }

    /** @test */
    public function it_can_suspend_and_resume_cart()
    {
        $cart = POSCart::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'active'
        ]);

        // Suspendre le panier
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}/pos/carts/{$cart->id}/suspend");

        $response->assertStatus(200);

        $cart->refresh();
        $this->assertEquals('suspended', $cart->status);

        // Reprendre le panier
        $response = $this->putJson("/api/restaurants/{$this->restaurant->id}/pos/carts/{$cart->id}/resume");

        $response->assertStatus(200);

        $cart->refresh();
        $this->assertEquals('active', $cart->status);
    }

    /** @test */
    public function it_calculates_cart_totals_correctly()
    {
        $session = POSSession::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'status' => 'active'
        ]);

        $menuItems = MenuItem::factory()->count(2)->create([
            'restaurant_id' => $this->restaurant->id
        ]);

        $data = [
            'session_id' => $session->id,
            'items' => [
                [
                    'menu_item_id' => $menuItems[0]->id,
                    'quantity' => 2,
                    'unit_price' => 10.00
                ],
                [
                    'menu_item_id' => $menuItems[1]->id,
                    'quantity' => 1,
                    'unit_price' => 15.50
                ]
            ],
            'discount_percent' => 10,
            'tax_percent' => 20
        ];

        $response = $this->postJson("/api/restaurants/{$this->restaurant->id}/pos/carts", $data);

        $response->assertStatus(201);

        $expectedSubtotal = (10.00 * 2) + 15.50; // 35.50
        $expectedDiscountAmount = $expectedSubtotal * 0.10; // 3.55
        $subtotalAfterDiscount = $expectedSubtotal - $expectedDiscountAmount; // 31.95
        $expectedTaxAmount = $subtotalAfterDiscount * 0.20; // 6.39
        $expectedTotal = $subtotalAfterDiscount + $expectedTaxAmount; // 38.34

        $cart = POSCart::latest()->first();
        $this->assertEquals(35.50, $cart->subtotal);
        $this->assertEquals(38.34, round($cart->total, 2));
    }
}
