<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CustomerAddress[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wishlist[] $wishlist
 * @method \Illuminate\Database\Eloquent\Relations\HasMany addresses()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany wishlist()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
        'mobile',
        'image',
        'is_available',
        'is_deleted',
        'role_id',
        'store_id',
        'type',
        'description',
        'token',
        'city_id',
        'area_id',
        'vendor_id',
        'plan_id',
        'purchase_amount',
        'purchase_date',
        'allow_without_subscription',
        'is_verified',
        'available_on_landing',
        'payment_id',
        'payment_type',
        'free_plan',
        'is_delivery',
        'google_id',
        'facebook_id',
        'apple_id',
        'login_type',
        'license_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relations pour les addons
     */

    /**
     * Relation avec le restaurant (pour les propriétaires)
     */
    public function restaurant()
    {
        return $this->hasOne(Restaurant::class, 'vendor_id');
    }

    /**
     * Relation avec les restaurants en tant qu'employé
     */
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_users');
    }

    /**
     * Tokens d'appareils pour Firebase
     */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Sessions POS
     */
    public function posSessions()
    {
        return $this->hasMany(POSSession::class);
    }

    /**
     * Panier POS
     */
    public function posCartItems()
    {
        return $this->hasMany(POSCart::class);
    }

    /**
     * Jobs d'import
     */
    public function importJobs()
    {
        return $this->hasMany(ImportJob::class);
    }

    /**
     * Jobs d'export
     */
    public function exportJobs()
    {
        return $this->hasMany(ExportJob::class);
    }

    /**
     * Commandes créées
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relation membre fidélité (si c'est un client)
     */
    public function loyaltyMember()
    {
        return $this->hasOne(LoyaltyMember::class, 'customer_id');
    }

    /**
     * Adresses du client
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Wishlist du client (produits favoris)
     */
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
