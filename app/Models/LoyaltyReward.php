<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'tier_id',
        'title',
        'description',
        'reward_type',
        'reward_value',
        'points_required',
        'image_url',
        'terms_conditions',
        'usage_limit',
        'usage_limit_per_member',
        'valid_from',
        'valid_until',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'reward_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_member' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'sort_order' => 'integer',
        'terms_conditions' => 'array',
    ];

    /**
     * Types de récompenses
     */
    const TYPE_DISCOUNT_PERCENTAGE = 'discount_percentage';
    const TYPE_DISCOUNT_FIXED = 'discount_fixed';
    const TYPE_FREE_ITEM = 'free_item';
    const TYPE_FREE_DELIVERY = 'free_delivery';
    const TYPE_CASHBACK = 'cashback';
    const TYPE_SPECIAL_OFFER = 'special_offer';

    /**
     * Restaurant auquel appartient cette récompense
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Niveau requis pour cette récompense (optionnel)
     */
    public function tier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'tier_id');
    }

    /**
     * Échanges de cette récompense
     */
    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class, 'reward_id');
    }

    /**
     * Scope pour les récompenses actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les récompenses valides (dans la période)
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->where('valid_from', '<=', now())
              ->orWhereNull('valid_from');
        })->where(function($q) {
            $q->where('valid_until', '>=', now())
              ->orWhereNull('valid_until');
        });
    }

    /**
     * Scope par type de récompense
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('reward_type', $type);
    }

    /**
     * Scope pour les récompenses disponibles pour un niveau
     */
    public function scopeForTier($query, $tierId = null)
    {
        return $query->where(function($q) use ($tierId) {
            $q->whereNull('tier_id')
              ->orWhere('tier_id', $tierId);
        });
    }

    /**
     * Vérifier si la récompense est valide
     */
    public function isValid()
    {
        $now = now();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->valid_from && $this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Vérifier si la récompense est disponible
     */
    public function isAvailable()
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->usage_limit && $this->getTotalUsageCount() >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir le nombre total d'utilisations
     */
    public function getTotalUsageCount()
    {
        return $this->redemptions()->where('status', '!=', 'cancelled')->count();
    }

    /**
     * Obtenir le nombre d'utilisations par un membre spécifique
     */
    public function getUsageCount($memberId)
    {
        return $this->redemptions()
            ->where('member_id', $memberId)
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    /**
     * Vérifier si un membre peut utiliser cette récompense
     */
    public function canBeUsedBy($member)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        // Vérifier les points nécessaires
        if ($member->points_balance < $this->points_required) {
            return false;
        }

        // Vérifier le niveau requis
        if ($this->tier_id && (!$member->tier || $member->tier->min_points < $this->tier->min_points)) {
            return false;
        }

        // Vérifier la limite par membre
        if ($this->usage_limit_per_member && $this->getUsageCount($member->id) >= $this->usage_limit_per_member) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir le libellé du type de récompense
     */
    public function getTypeLabel()
    {
        return match($this->reward_type) {
            self::TYPE_DISCOUNT_PERCENTAGE => 'Remise en pourcentage',
            self::TYPE_DISCOUNT_FIXED => 'Remise fixe',
            self::TYPE_FREE_ITEM => 'Article gratuit',
            self::TYPE_FREE_DELIVERY => 'Livraison gratuite',
            self::TYPE_CASHBACK => 'Cashback',
            self::TYPE_SPECIAL_OFFER => 'Offre spéciale',
            default => 'Récompense',
        };
    }

    /**
     * Obtenir la valeur formatée de la récompense
     */
    public function getFormattedValue()
    {
        return match($this->reward_type) {
            self::TYPE_DISCOUNT_PERCENTAGE => $this->reward_value . '%',
            self::TYPE_DISCOUNT_FIXED => number_format($this->reward_value, 2) . '€',
            self::TYPE_CASHBACK => number_format($this->reward_value, 2) . '€',
            default => $this->reward_value,
        };
    }

    /**
     * Obtenir la couleur associée au type
     */
    public function getTypeColor()
    {
        return match($this->reward_type) {
            self::TYPE_DISCOUNT_PERCENTAGE, self::TYPE_DISCOUNT_FIXED => 'blue',
            self::TYPE_FREE_ITEM, self::TYPE_FREE_DELIVERY => 'green',
            self::TYPE_CASHBACK => 'purple',
            self::TYPE_SPECIAL_OFFER => 'orange',
            default => 'gray',
        };
    }

    /**
     * Format pour l'affichage
     */
    public function toDisplayArray($member = null)
    {
        $canRedeem = $member ? $this->canBeUsedBy($member) : true;
        $reason = '';

        if ($member && !$canRedeem) {
            if ($member->points_balance < $this->points_required) {
                $reason = 'Points insuffisants';
            } elseif ($this->tier_id && (!$member->tier || $member->tier->min_points < $this->tier->min_points)) {
                $reason = 'Niveau requis: ' . $this->tier->name;
            } elseif ($this->usage_limit_per_member && $this->getUsageCount($member->id) >= $this->usage_limit_per_member) {
                $reason = 'Limite d\'utilisation atteinte';
            } elseif (!$this->isAvailable()) {
                $reason = 'Récompense non disponible';
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'reward_type' => $this->reward_type,
            'type_label' => $this->getTypeLabel(),
            'type_color' => $this->getTypeColor(),
            'reward_value' => $this->reward_value,
            'formatted_value' => $this->getFormattedValue(),
            'points_required' => $this->points_required,
            'image_url' => $this->image_url,
            'tier_required' => $this->tier?->name,
            'valid_until' => $this->valid_until,
            'valid_until_human' => $this->valid_until?->diffForHumans(),
            'usage_count' => $this->getTotalUsageCount(),
            'usage_limit' => $this->usage_limit,
            'can_redeem' => $canRedeem,
            'reason' => $reason,
            'is_valid' => $this->isValid(),
            'is_available' => $this->isAvailable(),
        ];
    }
}
