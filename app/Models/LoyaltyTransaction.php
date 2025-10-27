<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'restaurant_id',
        'order_id',
        'type',
        'points',
        'balance_after',
        'description',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Types de transactions
     */
    const TYPE_WELCOME_BONUS = 'welcome_bonus';
    const TYPE_ORDER_PURCHASE = 'order_purchase';
    const TYPE_REFERRAL_BONUS = 'referral_bonus';
    const TYPE_BIRTHDAY_BONUS = 'birthday_bonus';
    const TYPE_CHALLENGE_COMPLETION = 'challenge_completion';
    const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';
    const TYPE_REWARD_REDEMPTION = 'reward_redemption';
    const TYPE_POINTS_EXPIRY = 'points_expiry';

    /**
     * Membre concerné par cette transaction
     */
    public function member()
    {
        return $this->belongsTo(LoyaltyMember::class, 'member_id');
    }

    /**
     * Restaurant concerné
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Commande liée (si applicable)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope pour les gains de points
     */
    public function scopeEarnings($query)
    {
        return $query->where('points', '>', 0);
    }

    /**
     * Scope pour les dépenses de points
     */
    public function scopeSpending($query)
    {
        return $query->where('points', '<', 0);
    }

    /**
     * Scope pour les points expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope pour les points qui expirent bientôt
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    /**
     * Scope par type de transaction
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Vérifier si la transaction est expirée
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifier si la transaction expire bientôt
     */
    public function expiringSoon($days = 30)
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isFuture() &&
               $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Obtenir le libellé du type de transaction
     */
    public function getTypeLabel()
    {
        return match($this->type) {
            self::TYPE_WELCOME_BONUS => 'Bonus de bienvenue',
            self::TYPE_ORDER_PURCHASE => 'Achat',
            self::TYPE_REFERRAL_BONUS => 'Bonus parrainage',
            self::TYPE_BIRTHDAY_BONUS => 'Bonus anniversaire',
            self::TYPE_CHALLENGE_COMPLETION => 'Défi complété',
            self::TYPE_ADMIN_ADJUSTMENT => 'Ajustement manuel',
            self::TYPE_REWARD_REDEMPTION => 'Échange récompense',
            self::TYPE_POINTS_EXPIRY => 'Expiration points',
            default => 'Transaction',
        };
    }

    /**
     * Obtenir la couleur associée au type
     */
    public function getTypeColor()
    {
        if ($this->points > 0) {
            return 'green'; // Gain de points
        } else {
            return 'red'; // Dépense de points
        }
    }

    /**
     * Format pour l'affichage
     */
    public function toDisplayArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'type_color' => $this->getTypeColor(),
            'points' => $this->points,
            'points_formatted' => ($this->points > 0 ? '+' : '') . $this->points,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'order_number' => $this->order?->order_number,
            'expires_at' => $this->expires_at,
            'expires_at_human' => $this->expires_at?->diffForHumans(),
            'is_expired' => $this->isExpired(),
            'expiring_soon' => $this->expiringSoon(),
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
