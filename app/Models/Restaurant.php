<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_name',
        'restaurant_slug',
        'restaurant_address',
        'restaurant_phone',
        'restaurant_email',
        'restaurant_image',
        'description',
        'latitude',
        'longitude',
        'is_active',
        'delivery_fee',
        'minimum_order',
        'delivery_time',
        'opening_time',
        'closing_time',
        'is_open',
        'rating',
        'total_reviews',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_open' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'rating' => 'decimal:1',
    ];

    /**
     * Get restaurant owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get restaurant categories
     */
    public function categories()
    {
        return $this->hasMany(Category::class, 'user_id', 'user_id');
    }

    /**
     * Get restaurant items
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'user_id', 'user_id');
    }

    /**
     * Get restaurant orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    /**
     * Get restaurant tables
     */
    public function tables()
    {
        return $this->hasMany(Table::class, 'user_id', 'user_id');
    }

    /**
     * Get restaurant QR codes
     */
    public function tableQRs()
    {
        return $this->hasMany(TableQR::class, 'user_id', 'user_id');
    }

    /**
     * Check if restaurant is open
     */
    public function isOpen()
    {
        if (!$this->is_open || !$this->is_active) {
            return false;
        }

        $now = now()->format('H:i');
        return $now >= $this->opening_time && $now <= $this->closing_time;
    }

    /**
     * Get average rating
     */
    public function getAverageRating()
    {
        return $this->orders()
            ->whereNotNull('rating')
            ->avg('rating') ?: 0;
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersCount()
    {
        return $this->orders()->count();
    }

    /**
     * Relations pour les nouveaux addons
     */

    /**
     * Terminaux POS
     */
    public function posTerminals()
    {
        return $this->hasMany(POSTerminal::class);
    }

    /**
     * Sessions POS
     */
    public function posSessions()
    {
        return $this->hasMany(POSSession::class);
    }

    /**
     * Programme de fidélité
     */
    public function loyaltyProgram()
    {
        return $this->hasOne(LoyaltyProgram::class);
    }

    /**
     * Membres du programme de fidélité
     */
    public function loyaltyMembers()
    {
        return $this->hasMany(LoyaltyMember::class);
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
     * Notifications du restaurant
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * QR Codes des tables
     */
    public function tableQrCodes()
    {
        return $this->hasMany(TableQrCode::class);
    }

    /**
     * Éléments du menu avec relations
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Employés du restaurant
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'restaurant_users', 'restaurant_id', 'user_id');
    }
}
