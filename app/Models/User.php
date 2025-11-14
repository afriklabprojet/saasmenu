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
     *
     * Security: Reduced from 32 to 13 fields
     * Sensitive fields moved to $guarded
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'image',
        'description',
        'city_id',
        'area_id',
        'google_id',
        'facebook_id',
        'apple_id',
        'login_type',
        'slug',
    ];

    /**
     * The attributes that are NOT mass assignable.
     * These fields should only be modified through specific business logic.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'role_id',           // Security: Role should be assigned by admin only
        'type',              // Security: User type (admin/vendor/customer) should be controlled
        'is_verified',       // Security: Email verification should be handled by verification flow
        'is_available',      // Business logic: Should be managed by admin
        'is_deleted',        // Business logic: Soft delete should use proper methods
        'store_id',          // Business logic: Store assignment should be controlled
        'vendor_id',         // Business logic: Vendor assignment should be controlled
        'plan_id',           // Business logic: Subscription plan assignment
        'purchase_amount',   // Security: Payment amount should not be user-modifiable
        'purchase_date',     // Security: Payment date should not be user-modifiable
        'payment_id',        // Security: Payment gateway ID should not be user-modifiable
        'payment_type',      // Security: Payment type should be validated
        'free_plan',         // Security: Free plan status should be controlled
        'allow_without_subscription', // Business logic: Admin control
        'available_on_landing',      // Business logic: Admin control
        'is_delivery',       // Business logic: Delivery availability control
        'license_type',      // Business logic: License management
        'token',             // Security: API/Reset tokens should be generated, not assigned
        'remember_token',    // Security: Laravel internal
        'created_at',
        'updated_at',
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
