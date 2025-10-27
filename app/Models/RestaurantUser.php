<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantUser extends Model
{
    use HasFactory;

    protected $table = 'restaurant_users';

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'role',
        'permissions',
        'is_active',
        'joined_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'joined_at' => 'datetime'
    ];

    /**
     * Relation avec User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec Restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un rôle spécifique
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
