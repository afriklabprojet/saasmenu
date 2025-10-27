<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'is_active',
        'type',
        'points_per_currency',
        'currency_per_point',
        'min_points_redemption',
        'points_expiry_months',
        'tiers',
        'rules',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points_per_currency' => 'decimal:2',
        'currency_per_point' => 'decimal:4',
        'tiers' => 'array',
        'rules' => 'array',
        'settings' => 'array',
    ];

    /**
     * Relations
     */

    /**
     * Restaurant propriétaire
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Niveaux du programme
     */
    public function tiers()
    {
        return $this->hasMany(LoyaltyTier::class, 'program_id');
    }

    /**
     * Membres du programme
     */
    public function members()
    {
        return $this->hasMany(LoyaltyMember::class, 'program_id');
    }

    /**
     * Récompenses du programme
     */
    public function rewards()
    {
        return $this->hasMany(LoyaltyReward::class, 'program_id');
    }

    /**
     * Transactions de points
     */
    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'program_id');
    }

    /**
     * Scopes
     */

    /**
     * Programmes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accesseurs
     */

    /**
     * Calculer les points pour un montant donné
     */
    public function calculatePoints($amount)
    {
        return intval($amount * $this->points_per_currency);
    }

    /**
     * Calculer la valeur monétaire de points
     */
    public function calculateValue($points)
    {
        return $points * $this->currency_per_point;
    }

    /**
     * Vérifier si assez de points pour échange
     */
    public function canRedeem($points)
    {
        return $points >= $this->min_points_redemption;
    }
}