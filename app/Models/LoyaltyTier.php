<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'min_points',
        'points_multiplier',
        'benefits',
        'color',
        'icon',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'points_multiplier' => 'decimal:2',
        'benefits' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Restaurant auquel appartient ce niveau
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Membres ayant ce niveau
     */
    public function members()
    {
        return $this->hasMany(LoyaltyMember::class, 'tier_id');
    }

    /**
     * Récompenses exclusives à ce niveau
     */
    public function exclusiveRewards()
    {
        return $this->hasMany(LoyaltyReward::class, 'tier_id');
    }

    /**
     * Scope pour les niveaux actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('min_points');
    }

    /**
     * Obtenir le niveau suivant
     */
    public function getNextTier()
    {
        return static::where('restaurant_id', $this->restaurant_id)
            ->where('min_points', '>', $this->min_points)
            ->orderBy('min_points')
            ->first();
    }

    /**
     * Obtenir le niveau précédent
     */
    public function getPreviousTier()
    {
        return static::where('restaurant_id', $this->restaurant_id)
            ->where('min_points', '<', $this->min_points)
            ->orderBy('min_points', 'desc')
            ->first();
    }

    /**
     * Vérifier si c'est le niveau maximum
     */
    public function isMaxTier()
    {
        return !$this->getNextTier();
    }

    /**
     * Vérifier si c'est le niveau de base
     */
    public function isBaseTier()
    {
        return !$this->getPreviousTier();
    }

    /**
     * Obtenir les avantages formatés
     */
    public function getFormattedBenefits()
    {
        if (!$this->benefits) {
            return [];
        }

        return collect($this->benefits)->map(function($benefit) {
            return [
                'title' => $benefit['title'] ?? '',
                'description' => $benefit['description'] ?? '',
                'icon' => $benefit['icon'] ?? 'gift',
            ];
        });
    }

    /**
     * Format pour l'affichage
     */
    public function toDisplayArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'min_points' => $this->min_points,
            'points_multiplier' => $this->points_multiplier,
            'color' => $this->color,
            'icon' => $this->icon,
            'benefits' => $this->getFormattedBenefits(),
            'members_count' => $this->members()->count(),
            'is_base_tier' => $this->isBaseTier(),
            'is_max_tier' => $this->isMaxTier(),
            'next_tier' => $this->getNextTier()?->name,
        ];
    }
}