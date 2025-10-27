<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyNotification;
use App\Models\Restaurant;
use Carbon\Carbon;

class LoyaltyService
{
    /**
     * Ajouter des points à un membre
     */
    public function addPoints($member, $points, $type = 'manual', $description = '', $metadata = [])
    {
        try {
            DB::beginTransaction();

            // Créer la transaction
            $transaction = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'restaurant_id' => $member->restaurant_id,
                'type' => $type,
                'points' => $points,
                'balance_after' => $member->points_balance + $points,
                'description' => $description,
                'metadata' => $metadata,
                'expires_at' => $this->calculateExpirationDate($type),
            ]);

            // Mettre à jour les soldes du membre
            $member->increment('points_balance', $points);
            $member->increment('lifetime_points', $points);
            $member->touch('last_activity_at');

            // Vérifier la montée de niveau
            $this->checkTierUpgrade($member);

            // Créer une notification si points significatifs
            if ($points >= 50) {
                $this->createNotification($member, 'points_earned', [
                    'points' => $points,
                    'type' => $type,
                    'description' => $description,
                ]);
            }

            DB::commit();

            Log::info("Points ajoutés: {$points} pour membre {$member->id} ({$type})");
            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout points: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Déduire des points d'un membre
     */
    public function deductPoints($member, $points, $type = 'redemption', $description = '', $metadata = [])
    {
        if ($member->points_balance < $points) {
            throw new \Exception('Solde de points insuffisant');
        }

        try {
            DB::beginTransaction();

            // Créer la transaction
            $transaction = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'restaurant_id' => $member->restaurant_id,
                'type' => $type,
                'points' => -$points,
                'balance_after' => $member->points_balance - $points,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            // Mettre à jour le solde du membre
            $member->decrement('points_balance', $points);
            $member->touch('last_activity_at');

            DB::commit();

            Log::info("Points déduits: {$points} pour membre {$member->id} ({$type})");
            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur déduction points: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculer les points à partir d'un montant d'achat
     */
    public function calculatePointsFromAmount($amount, $restaurant)
    {
        if (!$restaurant || !$restaurant->loyalty_program_active) {
            return 0;
        }

        // Règle par défaut: 1 point par euro dépensé
        $pointsPerEuro = $restaurant->loyalty_points_per_euro ?? 1;
        $basePoints = floor($amount * $pointsPerEuro);

        return max(0, $basePoints);
    }

    /**
     * Vérifier et effectuer une montée de niveau
     */
    public function checkTierUpgrade($member)
    {
        $currentTier = $member->tier;

        $newTier = LoyaltyTier::where('restaurant_id', $member->restaurant_id)
            ->where('min_points', '<=', $member->lifetime_points)
            ->where('status', 'active')
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newTier && (!$currentTier || $newTier->id !== $currentTier->id)) {
            $member->update(['tier_id' => $newTier->id]);

            // Créer une notification de montée de niveau
            $this->createNotification($member, 'tier_upgrade', [
                'old_tier' => $currentTier?->name,
                'new_tier' => $newTier->name,
                'benefits' => $newTier->benefits,
            ]);

            // Points bonus pour la montée de niveau
            $bonusPoints = $newTier->upgrade_bonus_points ?? 0;
            if ($bonusPoints > 0) {
                $this->addPoints(
                    $member,
                    $bonusPoints,
                    'tier_upgrade_bonus',
                    "Bonus montée niveau {$newTier->name}"
                );
            }

            Log::info("Membre {$member->id} promu au niveau {$newTier->name}");
            return true;
        }

        return false;
    }

    /**
     * Traiter les points d'anniversaire
     */
    public function processBirthdayBonus()
    {
        $birthdayMembers = LoyaltyMember::birthdayToday()
            ->active()
            ->with('restaurant')
            ->get();

        foreach ($birthdayMembers as $member) {
            // Vérifier si le bonus n'a pas déjà été accordé cette année
            $alreadyGiven = LoyaltyTransaction::where('member_id', $member->id)
                ->where('type', 'birthday_bonus')
                ->whereYear('created_at', now()->year)
                ->exists();

            if (!$alreadyGiven) {
                $bonusPoints = $member->restaurant->loyalty_birthday_bonus ?? 100;

                $this->addPoints(
                    $member,
                    $bonusPoints,
                    'birthday_bonus',
                    'Bonus anniversaire'
                );

                // Créer notification spéciale
                $this->createNotification($member, 'birthday', [
                    'bonus_points' => $bonusPoints,
                ]);

                Log::info("Bonus anniversaire accordé au membre {$member->id}");
            }
        }
    }

    /**
     * Traiter l'expiration des points
     */
    public function processPointsExpiration()
    {
        $expiringTransactions = LoyaltyTransaction::where('expires_at', '<=', now())
            ->where('points', '>', 0)
            ->whereNull('expired_at')
            ->with('member')
            ->get();

        foreach ($expiringTransactions as $transaction) {
            try {
                DB::beginTransaction();

                // Déduire les points expirés
                $member = $transaction->member;
                $expiredPoints = $transaction->points;

                if ($member->points_balance >= $expiredPoints) {
                    $this->deductPoints(
                        $member,
                        $expiredPoints,
                        'points_expiry',
                        "Expiration points du {$transaction->created_at->format('d/m/Y')}"
                    );
                }

                // Marquer la transaction comme expirée
                $transaction->update(['expired_at' => now()]);

                // Notification d'expiration
                $this->createNotification($member, 'points_expired', [
                    'expired_points' => $expiredPoints,
                    'remaining_balance' => $member->fresh()->points_balance,
                ]);

                DB::commit();
                Log::info("Points expirés: {$expiredPoints} pour membre {$member->id}");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Erreur expiration points pour transaction {$transaction->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Calculer les statistiques d'un restaurant
     */
    public function getRestaurantStats($restaurantId, $period = 'month')
    {
        $dateFilter = match($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };

        $stats = [
            // Membres
            'total_members' => LoyaltyMember::where('restaurant_id', $restaurantId)->count(),
            'active_members' => LoyaltyMember::where('restaurant_id', $restaurantId)
                ->active()
                ->count(),
            'new_members' => LoyaltyMember::where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', $dateFilter)
                ->count(),

            // Points
            'total_points_issued' => LoyaltyTransaction::where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', $dateFilter)
                ->where('points', '>', 0)
                ->sum('points'),
            'total_points_redeemed' => LoyaltyTransaction::where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', $dateFilter)
                ->where('points', '<', 0)
                ->sum('points'),

            // Récompenses
            'rewards_redeemed' => DB::table('loyalty_redemptions')
                ->where('restaurant_id', $restaurantId)
                ->whereBetween('created_at', $dateFilter)
                ->count(),

            // Taux d'engagement
            'engagement_rate' => $this->calculateEngagementRate($restaurantId, $dateFilter),
        ];

        return $stats;
    }

    /**
     * Obtenir les meilleurs membres
     */
    public function getTopMembers($restaurantId, $limit = 10, $period = 'all')
    {
        $query = LoyaltyMember::where('restaurant_id', $restaurantId)
            ->active()
            ->with('tier');

        if ($period !== 'all') {
            $dateFilter = match($period) {
                'month' => now()->startOfMonth(),
                'year' => now()->startOfYear(),
                default => now()->startOfMonth()
            };

            $query->where('created_at', '>=', $dateFilter);
        }

        return $query->orderBy('lifetime_points', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Créer une notification pour un membre
     */
    private function createNotification($member, $type, $data = [])
    {
        $notifications = [
            'points_earned' => [
                'title' => 'Points gagnés !',
                'message' => "Vous avez gagné {$data['points']} points !",
            ],
            'tier_upgrade' => [
                'title' => 'Félicitations !',
                'message' => "Vous êtes maintenant membre {$data['new_tier']} !",
            ],
            'birthday' => [
                'title' => 'Joyeux anniversaire !',
                'message' => "Nous vous offrons {$data['bonus_points']} points pour votre anniversaire !",
            ],
            'points_expired' => [
                'title' => 'Points expirés',
                'message' => "{$data['expired_points']} points ont expiré. Solde actuel: {$data['remaining_balance']} points.",
            ],
        ];

        $notificationData = $notifications[$type] ?? [
            'title' => 'Notification',
            'message' => 'Nouvelle notification de fidélité',
        ];

        return LoyaltyNotification::create([
            'member_id' => $member->id,
            'restaurant_id' => $member->restaurant_id,
            'type' => $type,
            'title' => $notificationData['title'],
            'message' => $notificationData['message'],
            'data' => $data,
        ]);
    }

    /**
     * Calculer la date d'expiration des points
     */
    private function calculateExpirationDate($type)
    {
        return match($type) {
            'welcome_bonus', 'birthday_bonus', 'referral_bonus' => now()->addYear(),
            'challenge_completion' => now()->addMonths(6),
            'admin_adjustment' => now()->addYear(),
            default => null, // Points d'achat n'expirent pas
        };
    }

    /**
     * Calculer le taux d'engagement
     */
    private function calculateEngagementRate($restaurantId, $dateFilter)
    {
        $totalMembers = LoyaltyMember::where('restaurant_id', $restaurantId)->count();

        if ($totalMembers === 0) {
            return 0;
        }

        $activeMembers = LoyaltyMember::where('restaurant_id', $restaurantId)
            ->whereBetween('last_activity_at', $dateFilter)
            ->count();

        return round(($activeMembers / $totalMembers) * 100, 2);
    }

    /**
     * Suggérer des récompenses pour un montant de panier
     */
    public function suggestRewardsForAmount($member, $amount)
    {
        if (!$member) {
            return collect();
        }

        return LoyaltyReward::where('restaurant_id', $member->restaurant_id)
            ->active()
            ->valid()
            ->forTier($member->tier_id)
            ->where('points_required', '<=', $member->points_balance)
            ->whereIn('reward_type', ['discount_percentage', 'discount_fixed'])
            ->orderBy('points_required')
            ->get()
            ->filter(function($reward) use ($member) {
                return $reward->canBeUsedBy($member);
            });
    }

    /**
     * Calculer la valeur monétaire d'une récompense
     */
    public function calculateRewardValue($reward, $orderAmount)
    {
        return match($reward->reward_type) {
            'discount_percentage' => min($orderAmount * ($reward->reward_value / 100), $orderAmount),
            'discount_fixed' => min($reward->reward_value, $orderAmount),
            'free_delivery' => $reward->reward_value ?? 0,
            'cashback' => $reward->reward_value,
            default => 0,
        };
    }

    /**
     * Obtenir les paramètres de fidélité
     */
    public function getSettings()
    {
        return [
            'points_per_euro' => config('loyalty.points_per_euro', 1),
            'euro_per_point' => config('loyalty.euro_per_point', 0.01),
            'welcome_points' => config('loyalty.welcome_points', 100),
            'birthday_points' => config('loyalty.birthday_points', 50),
            'referral_points' => config('loyalty.referral_points', 200),
            'review_points' => config('loyalty.review_points', 25),
            'social_share_points' => config('loyalty.social_share_points', 10),
            'min_order_for_points' => config('loyalty.min_order_for_points', 10),
            'points_expiry_days' => config('loyalty.points_expiry_days', 365),
            'is_active' => config('loyalty.is_active', true),
        ];
    }

    /**
     * Mettre à jour les paramètres de fidélité
     */
    public function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            config(['loyalty.' . $key => $value]);
        }

        // Sauvegarder dans la base de données ou fichier de configuration
        // Implémentation dépendante de votre système de stockage des paramètres
        return true;
    }
}
