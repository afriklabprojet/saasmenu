<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'postal_code',
        'status',
        'email_verified_at',
        'addresses',
        'notification_preferences',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'addresses' => 'array',
        'notification_preferences' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Get customer orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get customer loyalty cards
     */
    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    /**
     * Get customer favorites
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get customer notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if customer is active
     */
    public function isActive()
    {
        return $this->status == 1;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->city, $this->postal_code]);
        return implode(', ', $parts);
    }
}
