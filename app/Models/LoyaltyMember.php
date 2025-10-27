<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'member_code',
        'points_balance',
        'lifetime_points',
        'tier_id',
        'referral_code',
        'joined_at',
        'last_activity_at',
        'status',
        'preferences',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'joined_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'points_balance' => 'integer',
        'lifetime_points' => 'integer',
        'preferences' => 'array',
    ];

    protected $appends = [
        'tier_progress',
        'points_to_next_tier',
        'membership_duration',
    ];

    /**
     * Status possibles pour un membre
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Restaurant auquel appartient ce membre
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Utilisateur lié (si connecté)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Niveau de fidélité actuel
     */
    public function tier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'tier_id');
    }

    /**
     * Transactions de points de ce membre
     */
    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'member_id');
    }

    /**
     * Échanges de récompenses de ce membre
     */
    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class, 'member_id');
    }

    /**
     * Notifications de fidélité pour ce membre
     */
    public function notifications()
    {
        return $this->hasMany(LoyaltyNotification::class, 'member_id');
    }

    /**
     * Parrainages effectués par ce membre
     */
    public function referrals()
    {
        return $this->hasMany(LoyaltyReferral::class, 'referrer_id');
    }

    /**
     * Membre qui a parrainé celui-ci
     */
    public function referrer()
    {
        return $this->hasOneThrough(
            LoyaltyMember::class,
            LoyaltyReferral::class,
            'referred_id',
            'id',
            'id',
            'referrer_id'
        );
    }

    /**
     * Commandes de ce membre
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_email', 'email')
            ->where('restaurant_id', $this->restaurant_id);
    }

    /**
     * Scope pour les membres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pour les membres par restaurant
     */
    public function scopeForRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    /**
     * Scope pour les membres avec anniversaire aujourd'hui
     */
    public function scopeBirthdayToday($query)
    {
        return $query->whereMonth('birth_date', now()->month)
            ->whereDay('birth_date', now()->day);
    }

    /**
     * Scope pour les membres inactifs
     */
    public function scopeInactive($query, $days = 90)
    {
        return $query->where('last_activity_at', '<', now()->subDays($days))
            ->orWhereNull('last_activity_at');
    }

    /**
     * Progression vers le niveau suivant (pourcentage)
     */
    public function getTierProgressAttribute()
    {
        if (!$this->tier) {
            return 0;
        }

        $nextTier = LoyaltyTier::where('restaurant_id', $this->restaurant_id)
            ->where('min_points', '>', $this->tier->min_points)
            ->orderBy('min_points')
            ->first();

        if (!$nextTier) {
            return 100; // Niveau maximum atteint
        }

        $currentTierPoints = $this->tier->min_points;
        $nextTierPoints = $nextTier->min_points;
        $memberPoints = $this->lifetime_points;

        if ($memberPoints >= $nextTierPoints) {
            return 100;
        }

        $progress = (($memberPoints - $currentTierPoints) / ($nextTierPoints - $currentTierPoints)) * 100;
        return max(0, min(100, round($progress, 1)));
    }

    /**
     * Points nécessaires pour atteindre le niveau suivant
     */
    public function getPointsToNextTierAttribute()
    {
        if (!$this->tier) {
            return null;
        }

        $nextTier = LoyaltyTier::where('restaurant_id', $this->restaurant_id)
            ->where('min_points', '>', $this->tier->min_points)
            ->orderBy('min_points')
            ->first();

        if (!$nextTier) {
            return null; // Niveau maximum atteint
        }

        return max(0, $nextTier->min_points - $this->lifetime_points);
    }

    /**
     * Durée d'adhésion en jours
     */
    public function getMembershipDurationAttribute()
    {
        return $this->joined_at ? now()->diffInDays($this->joined_at) : 0;
    }

    /**
     * Ajouter des points
     */
    public function addPoints($points, $type = 'manual', $description = '', $metadata = [])
    {
        $transaction = LoyaltyTransaction::create([
            'member_id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'type' => $type,
            'points' => $points,
            'balance_after' => $this->points_balance + $points,
            'description' => $description,
            'metadata' => $metadata,
            'expires_at' => $this->calculateExpirationDate($type),
        ]);

        // Mettre à jour les soldes
        $this->increment('points_balance', $points);
        $this->increment('lifetime_points', $points);

        // Mettre à jour l'activité
        $this->touch('last_activity_at');

        // Vérifier le changement de niveau
        $this->checkTierUpgrade();

        return $transaction;
    }

    /**
     * Déduire des points
     */
    public function deductPoints($points, $type = 'redemption', $description = '', $metadata = [])
    {
        if ($this->points_balance < $points) {
            throw new \Exception('Solde de points insuffisant');
        }

        $transaction = LoyaltyTransaction::create([
            'member_id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'type' => $type,
            'points' => -$points,
            'balance_after' => $this->points_balance - $points,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        // Mettre à jour le solde
        $this->decrement('points_balance', $points);

        // Mettre à jour l'activité
        $this->touch('last_activity_at');

        return $transaction;
    }

    /**
     * Vérifier et effectuer la montée de niveau
     */
    public function checkTierUpgrade()
    {
        $newTier = LoyaltyTier::where('restaurant_id', $this->restaurant_id)
            ->where('min_points', '<=', $this->lifetime_points)
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newTier && $newTier->id !== $this->tier_id) {
            $oldTier = $this->tier;

            $this->update(['tier_id' => $newTier->id]);

            // Créer une notification
            LoyaltyNotification::create([
                'member_id' => $this->id,
                'restaurant_id' => $this->restaurant_id,
                'type' => 'tier_upgrade',
                'title' => 'Félicitations !',
                'message' => "Vous êtes maintenant membre {$newTier->name} !",
                'data' => [
                    'old_tier' => $oldTier?->name,
                    'new_tier' => $newTier->name,
                    'benefits' => $newTier->benefits,
                ],
            ]);

            return true;
        }

        return false;
    }

    /**
     * Calculer la date d'expiration des points
     */
    private function calculateExpirationDate($type)
    {
        // Certains types de points n'expirent pas
        if (in_array($type, ['purchase', 'order_purchase'])) {
            return null; // Points d'achat n'expirent pas
        }

        // Points bonus expirent après 1 an
        if (in_array($type, ['welcome_bonus', 'birthday_bonus', 'referral_bonus'])) {
            return now()->addYear();
        }

        // Points de défi expirent après 6 mois
        if ($type === 'challenge_completion') {
            return now()->addMonths(6);
        }

        return null; // Par défaut, pas d'expiration
    }

    /**
     * Obtenir le solde de points non expirés
     */
    public function getActivePointsBalance()
    {
        return $this->transactions()
            ->where('points', '>', 0)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->sum('points') - $this->getUsedPoints();
    }

    /**
     * Obtenir le total des points utilisés
     */
    public function getUsedPoints()
    {
        return abs($this->transactions()
            ->where('points', '<', 0)
            ->sum('points'));
    }

    /**
     * Vérifier si c'est l'anniversaire du membre
     */
    public function isBirthdayToday()
    {
        if (!$this->birth_date) {
            return false;
        }

        return $this->birth_date->month === now()->month &&
               $this->birth_date->day === now()->day;
    }

    /**
     * Obtenir les statistiques du membre
     */
    public function getStats($period = 'all')
    {
        $query = $this->transactions();

        if ($period === 'month') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', now()->year);
        }

        $transactions = $query->get();

        return [
            'total_earned' => $transactions->where('points', '>', 0)->sum('points'),
            'total_spent' => abs($transactions->where('points', '<', 0)->sum('points')),
            'transaction_count' => $transactions->count(),
            'avg_transaction' => $transactions->avg('points'),
            'last_transaction' => $transactions->sortByDesc('created_at')->first()?->created_at,
        ];
    }

    /**
     * Marquer comme actif
     */
    public function markActive()
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Générer un code de membre unique
     */
    public static function generateUniqueCode($restaurant)
    {
        do {
            $code = ($restaurant->code ?? 'RST') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (static::where('member_code', $code)->exists());

        return $code;
    }

    /**
     * Générer un code de parrainage unique
     */
    public static function generateUniqueReferralCode()
    {
        do {
            $code = 'REF' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }
}
