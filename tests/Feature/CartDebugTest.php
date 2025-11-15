<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Cart, Item, Category, Settings};
use Illuminate\Support\Facades\Session;

class CartDebugTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_cart_creation_and_retrieval()
    {
        // Créer vendor
        $vendor = User::factory()->create([
            'type' => 2,
            'slug' => 'test-vendor',
            'is_available' => 1,
        ]);

        // Créer customer
        $customer = User::factory()->create([
            'type' => 3,
            'is_available' => 1,
        ]);

        // Créer settings
        Settings::create([
            'vendor_id' => $vendor->id,
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        // Créer catégorie et item
        $category = Category::create([
            'vendor_id' => $vendor->id,
            'name' => 'Test',
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        $item = Item::create([
            'vendor_id' => $vendor->id,
            'cat_id' => $category->id,
            'name' => 'Test Item',
            'price' => 10,
            'is_available' => 1,
            'is_deleted' => 2,
        ]);

        // Créer panier
        $cart = Cart::create([
            'vendor_id' => $vendor->id,
            'user_id' => $customer->id,
            'item_id' => $item->id,
            'qty' => 2,
            'price' => $item->price,
        ]);

        $this->assertNotNull($cart->id);
        
        // Vérifier récupération
        $retrieved = Cart::where('vendor_id', $vendor->id)
            ->where('user_id', $customer->id)
            ->first();

        $this->assertNotNull($retrieved);
        $this->assertEquals($customer->id, $retrieved->user_id);

        // Simuler auth et session
        Session::put('restaurant_id', $vendor->id);
        $this->actingAs($customer);

        // Vérifier que Auth fonctionne
        $this->assertEquals($customer->id, auth()->id());

        // Tester la route v2 checkout
        $response = $this->actingAs($customer)
            ->withSession(['restaurant_id' => $vendor->id])
            ->get(route('v2.order.checkout'));
        
        echo "\nStatus: " . $response->status();
        
        if ($response->status() === 404) {
            // Afficher le contenu complet de l'erreur 404
            echo "\n404 Error - Content:\n";
            echo substr($response->content(), 0, 1000);
        } else {
            echo "\nContent preview: " . substr($response->content(), 0, 200);
        }
        
        // Voir si c'est une redirection
        if ($response->status() === 302) {
            echo "\nRedirect to: " . $response->headers->get('Location');
        }
    }
}
