<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use App\Models\CustomerAddress;
use App\Models\Wishlist;
use Carbon\Carbon;

/**
 * Contrôleur de gestion du compte client
 *
 * Ce contrôleur gère toutes les fonctionnalités du dashboard client :
 * - Vue d'ensemble avec statistiques
 * - Gestion du profil utilisateur
 * - Historique et détails des commandes
 * - Gestion des adresses de livraison
 * - Liste de souhaits (wishlist)
 *
 * @method \App\Models\User user() Retourne l'utilisateur authentifié avec relations addresses() et wishlist()
 */
class CustomerAccountController extends Controller
{
    /**
     * Constructor - Appliquer middleware auth et vérifier si la fonctionnalité est activée
     */
    public function __construct()
    {
        $this->middleware('auth');

        // Vérifier si le système de compte client est activé
        $this->middleware(function ($request, $next) {
            if (!config('customer.enabled', false)) {
                abort(404, 'Customer account system is disabled');
            }
            return $next($request);
        });
    }

    /**
     * Obtenir l'utilisateur authentifié avec le bon type
     *
     * @return User
     */
    private function getAuthUser(): User
    {
        /** @var User $user */
        $user = $this->getAuthUser();
        return $user;
    }

    /**
     * Dashboard client - Vue d'ensemble du compte
     */
    public function index()
    {
        $user = $this->getAuthUser();

        // Statistiques du client
        $stats = [
            'total_orders' => Order::where('client_id', $user->id)->count(),
            'pending_orders' => Order::where('client_id', $user->id)
                ->whereIn('orderstatus_id', [1, 2])
                ->count(),
            'completed_orders' => Order::where('client_id', $user->id)
                ->where('orderstatus_id', 5)
                ->count(),
            'total_spent' => Order::where('client_id', $user->id)
                ->where('orderstatus_id', 5)
                ->sum('order_price'),
        ];

        // Dernières commandes
        $recentOrders = Order::where('client_id', $user->id)
            ->with(['restorant', 'status'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Adresses sauvegardées
        $addresses = $user->addresses()->take(3)->get();

        // Produits favoris
        $wishlistCount = $user->wishlist()->count();

        return view('customer.dashboard', compact(
            'user',
            'stats',
            'recentOrders',
            'addresses',
            'wishlistCount'
        ));
    }

    /**
     * Afficher le profil du client
     */
    public function profile()
    {
        $user = $this->getAuthUser();
        return view('customer.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil du client
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getAuthUser();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.unique' => 'Cet email est déjà utilisé',
            'phone.required' => 'Le téléphone est obligatoire',
            'avatar.image' => 'Le fichier doit être une image',
            'avatar.max' => 'L\'image ne doit pas dépasser 2 Mo',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mise à jour des informations
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Upload de l'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Sauvegarder le nouveau
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('customer.profile')
            ->with('success', 'Profil mis à jour avec succès');
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire',
            'password.required' => 'Le nouveau mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = $this->getAuthUser();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Le mot de passe actuel est incorrect']);
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('customer.profile')
            ->with('success', 'Mot de passe changé avec succès');
    }

    /**
     * Afficher l'historique des commandes
     */
    public function orders(Request $request)
    {
        $user = $this->getAuthUser();

        $query = Order::where('client_id', $user->id)
            ->with(['restorant', 'status', 'orderitems']);

        // Filtres
        if ($request->has('status') && $request->status != '') {
            $query->where('orderstatus_id', $request->status);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('restorant', function($rq) use ($request) {
                      $rq->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        // Liste des statuts pour le filtre
        $statuses = DB::table('order_status')->get();

        return view('customer.orders', compact('orders', 'statuses'));
    }

    /**
     * Détails d'une commande
     */
    public function orderDetails($orderId)
    {
        $user = $this->getAuthUser();

        $order = Order::where('client_id', $user->id)
            ->where('id', $orderId)
            ->with(['restorant', 'status', 'orderitems.item', 'address'])
            ->firstOrFail();

        return view('customer.order-details', compact('order'));
    }

    /**
     * Recomander (dupliquer une commande)
     */
    public function reorder($orderId)
    {
        $user = $this->getAuthUser();

        $order = Order::where('client_id', $user->id)
            ->where('id', $orderId)
            ->with('orderitems')
            ->firstOrFail();

        // Ajouter les items au panier
        foreach ($order->orderitems as $item) {
            // Logique pour ajouter au panier
            // À adapter selon votre système de panier
        }

        return redirect()->route('cart.index')
            ->with('success', 'Articles ajoutés au panier');
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder($orderId)
    {
        $user = $this->getAuthUser();

        $order = Order::where('client_id', $user->id)
            ->where('id', $orderId)
            ->whereIn('orderstatus_id', [1, 2]) // Seulement en attente ou acceptée
            ->firstOrFail();

        // Mettre à jour le statut (6 = annulée par exemple)
        $order->orderstatus_id = 6;
        $order->save();

        // Déclencher événement pour notification
        // event(new OrderCancelledEvent($order));

        return redirect()->route('customer.orders')
            ->with('success', 'Commande annulée avec succès');
    }

    /**
     * Gestion des adresses
     */
    public function addresses()
    {
        $user = $this->getAuthUser();
        $addresses = $user->addresses()->get();

        return view('customer.addresses', compact('addresses'));
    }

    /**
     * Ajouter une adresse
     */
    public function storeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ], [
            'address_name.required' => 'Le nom de l\'adresse est obligatoire',
            'address.required' => 'L\'adresse est obligatoire',
            'phone.required' => 'Le téléphone est obligatoire',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = $this->getAuthUser();

        // Si c'est la première adresse ou marquée par défaut
        $isDefault = $request->has('is_default') || $user->addresses()->count() == 0;

        // Retirer le défaut des autres si nécessaire
        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'address_name' => $request->address_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_default' => $isDefault,
        ]);

        return redirect()->route('customer.addresses')
            ->with('success', 'Adresse ajoutée avec succès');
    }

    /**
     * Mettre à jour une adresse
     */
    public function updateAddress(Request $request, $addressId)
    {
        $user = $this->getAuthUser();

        $address = $user->addresses()->findOrFail($addressId);

        $validator = Validator::make($request->all(), [
            'address_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $address->address_name = $request->address_name;
        $address->address = $request->address;
        $address->phone = $request->phone;

        // Gérer l'adresse par défaut
        if ($request->has('is_default') && $request->is_default) {
            $user->addresses()->update(['is_default' => false]);
            $address->is_default = true;
        }

        $address->save();

        return redirect()->route('customer.addresses')
            ->with('success', 'Adresse mise à jour avec succès');
    }

    /**
     * Supprimer une adresse
     */
    public function deleteAddress($addressId)
    {
        $user = $this->getAuthUser();
        $address = $user->addresses()->findOrFail($addressId);

        // Si c'était l'adresse par défaut, marquer une autre
        if ($address->is_default && $user->addresses()->count() > 1) {
            $nextAddress = $user->addresses()
                ->where('id', '!=', $addressId)
                ->first();
            if ($nextAddress) {
                $nextAddress->is_default = true;
                $nextAddress->save();
            }
        }

        $address->delete();

        return redirect()->route('customer.addresses')
            ->with('success', 'Adresse supprimée avec succès');
    }

    /**
     * Wishlist (liste de favoris)
     */
    public function wishlist()
    {
        $user = $this->getAuthUser();
        $wishlist = $user->wishlist()->with('item')->get();

        return view('customer.wishlist', compact('wishlist'));
    }

    /**
     * Ajouter au wishlist
     */
    public function addToWishlist(Request $request)
    {
        $user = $this->getAuthUser();

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Produit invalide'
            ], 422);
        }

        // Vérifier si déjà dans la wishlist
        $exists = $user->wishlist()
            ->where('item_id', $request->item_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Produit déjà dans vos favoris'
            ]);
        }

        $user->wishlist()->create([
            'item_id' => $request->item_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté aux favoris'
        ]);
    }

    /**
     * Retirer du wishlist
     */
    public function removeFromWishlist($wishlistId)
    {
        $user = $this->getAuthUser();
        $wishlistItem = $user->wishlist()->findOrFail($wishlistId);
        $wishlistItem->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produit retiré des favoris'
            ]);
        }

        return redirect()->route('customer.wishlist')
            ->with('success', 'Produit retiré des favoris');
    }

    /**
     * Vider toute la wishlist
     */
    public function clearWishlist()
    {
        $user = $this->getAuthUser();
        $user->wishlist()->delete();

        return redirect()->route('customer.wishlist')
            ->with('success', 'Wishlist vidée avec succès');
    }
}
