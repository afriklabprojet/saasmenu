<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyChallenge;
use App\Models\LoyaltyNotification;
use App\Models\Restaurant;
use App\Models\Order;
use App\Services\LoyaltyService;
use Carbon\Carbon;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Inscription au programme de fidélité
     */
    public function joinProgram(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'referral_code' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $restaurant = Restaurant::find($request->restaurant_id);

            // Vérifier si le programme de fidélité est actif
            if (!$restaurant->loyalty_program_active) {
                return response()->json([
                    'error' => 'Le programme de fidélité n\'est pas actif pour ce restaurant'
                ], 400);
            }

            // Vérifier si le membre existe déjà
            $existingMember = LoyaltyMember::where('restaurant_id', $request->restaurant_id)
                ->where(function($query) use ($request) {
                    $query->where('email', $request->email)
                          ->orWhere('phone', $request->phone);
                })
                ->first();

            if ($existingMember) {
                return response()->json([
                    'error' => 'Un compte fidélité existe déjà avec cet email ou téléphone'
                ], 400);
            }

            // Créer le membre
            $member = LoyaltyMember::create([
                'restaurant_id' => $request->restaurant_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'member_code' => $this->generateMemberCode($restaurant),
                'points_balance' => 0,
                'tier_id' => $this->getBaseTier($restaurant->id)?->id,
                'joined_at' => now(),
                'referral_code' => $this->generateReferralCode(),
            ]);

            // Bonus de bienvenue
            $welcomeBonus = $restaurant->loyalty_welcome_bonus ?? 100;
            if ($welcomeBonus > 0) {
                $this->loyaltyService->addPoints(
                    $member,
                    $welcomeBonus,
                    'welcome_bonus',
                    'Bonus de bienvenue'
                );
            }

            // Traiter le parrainage si applicable
            if ($request->referral_code) {
                $this->processReferral($member, $request->referral_code);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'member' => [
                    'id' => $member->id,
                    'member_code' => $member->member_code,
                    'name' => $member->name,
                    'points_balance' => $member->points_balance,
                    'tier' => $member->tier?->name,
                    'referral_code' => $member->referral_code,
                ],
                'message' => "Bienvenue dans notre programme de fidélité ! Vous avez reçu {$welcomeBonus} points de bienvenue."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur inscription programme fidélité: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'inscription'], 500);
        }
    }

    /**
     * Consulter le solde de points
     */
    public function getBalance(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'identifier' => 'required|string', // email, phone ou member_code
        ]);

        $member = $this->findMember($request->restaurant_id, $request->identifier);

        if (!$member) {
            return response()->json(['error' => 'Membre non trouvé'], 404);
        }

        return response()->json([
            'member' => [
                'name' => $member->name,
                'member_code' => $member->member_code,
                'points_balance' => $member->points_balance,
                'tier' => [
                    'name' => $member->tier?->name,
                    'benefits' => $member->tier?->benefits,
                    'next_tier_points' => $this->getNextTierPoints($member),
                ],
                'lifetime_points' => $member->lifetime_points,
                'points_to_expire' => $this->getPointsToExpire($member),
            ]
        ]);
    }

    /**
     * Historique des transactions de points
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'identifier' => 'required|string',
            'limit' => 'nullable|integer|max:100',
        ]);

        $member = $this->findMember($request->restaurant_id, $request->identifier);

        if (!$member) {
            return response()->json(['error' => 'Membre non trouvé'], 404);
        }

        $transactions = LoyaltyTransaction::where('member_id', $member->id)
            ->with(['order'])
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 20))
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $this->getTransactionTypeLabel($transaction->type),
                    'points' => $transaction->points,
                    'description' => $transaction->description,
                    'order_number' => $transaction->order?->order_number,
                    'created_at' => $transaction->created_at->format('d/m/Y H:i'),
                    'created_at_human' => $transaction->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'member' => $member->name,
            'current_balance' => $member->points_balance,
            'transactions' => $transactions
        ]);
    }

    /**
     * Récompenses disponibles
     */
    public function getAvailableRewards(Request $request)
    {
        $restaurant_id = $request->get('restaurant_id');
        $member_id = $request->get('member_id');

        $query = LoyaltyReward::where('restaurant_id', $restaurant_id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('valid_until', '>', now())
                  ->orWhereNull('valid_until');
            });

        // Filtrer par niveau si membre connecté
        if ($member_id) {
            $member = LoyaltyMember::find($member_id);
            if ($member && $member->tier) {
                $query->where(function($q) use ($member) {
                    $q->whereNull('tier_id')
                      ->orWhere('tier_id', $member->tier_id)
                      ->orWhereIn('tier_id', function($subQuery) use ($member) {
                          $subQuery->select('id')
                              ->from('loyalty_tiers')
                              ->where('restaurant_id', $member->restaurant_id)
                              ->where('min_points', '<=', $member->tier->min_points);
                      });
                });
            }
        }

        $rewards = $query->orderBy('points_required')
            ->get()
            ->map(function($reward) use ($member_id) {
                $canRedeem = true;
                $reason = '';

                if ($member_id) {
                    $member = LoyaltyMember::find($member_id);
                    if ($member) {
                        if ($member->points_balance < $reward->points_required) {
                            $canRedeem = false;
                            $reason = 'Points insuffisants';
                        }
                        if ($reward->usage_limit && $reward->getUsageCount($member->id) >= $reward->usage_limit) {
                            $canRedeem = false;
                            $reason = 'Limite d\'utilisation atteinte';
                        }
                    }
                }

                return [
                    'id' => $reward->id,
                    'title' => $reward->title,
                    'description' => $reward->description,
                    'points_required' => $reward->points_required,
                    'reward_type' => $reward->reward_type,
                    'reward_value' => $reward->reward_value,
                    'image_url' => $reward->image_url,
                    'valid_until' => $reward->valid_until,
                    'usage_limit' => $reward->usage_limit,
                    'can_redeem' => $canRedeem,
                    'reason' => $reason,
                ];
            });

        return response()->json(['rewards' => $rewards]);
    }

    /**
     * Utiliser des points pour une récompense
     */
    public function redeemReward(Request $request, $reward_id)
    {
        $request->validate([
            'member_identifier' => 'required|string',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        try {
            DB::beginTransaction();

            $member = $this->findMember($request->restaurant_id, $request->member_identifier);
            if (!$member) {
                return response()->json(['error' => 'Membre non trouvé'], 404);
            }

            $reward = LoyaltyReward::where('id', $reward_id)
                ->where('restaurant_id', $request->restaurant_id)
                ->where('status', 'active')
                ->first();

            if (!$reward) {
                return response()->json(['error' => 'Récompense non trouvée'], 404);
            }

            // Vérifications
            if ($member->points_balance < $reward->points_required) {
                return response()->json(['error' => 'Points insuffisants'], 400);
            }

            if ($reward->valid_until && $reward->valid_until < now()) {
                return response()->json(['error' => 'Récompense expirée'], 400);
            }

            if ($reward->usage_limit && $reward->getUsageCount($member->id) >= $reward->usage_limit) {
                return response()->json(['error' => 'Limite d\'utilisation atteinte'], 400);
            }

            // Générer le code de récompense
            $redeemCode = $this->generateRedeemCode();

            // Déduire les points
            $this->loyaltyService->deductPoints(
                $member,
                $reward->points_required,
                'reward_redemption',
                "Échange: {$reward->title}",
                ['reward_id' => $reward->id, 'redeem_code' => $redeemCode]
            );

            // Enregistrer l'échange
            DB::table('loyalty_redemptions')->insert([
                'member_id' => $member->id,
                'reward_id' => $reward->id,
                'points_used' => $reward->points_required,
                'redeem_code' => $redeemCode,
                'status' => 'pending',
                'redeemed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'redeem_code' => $redeemCode,
                'reward' => $reward->title,
                'points_used' => $reward->points_required,
                'remaining_balance' => $member->fresh()->points_balance,
                'message' => 'Récompense échangée avec succès ! Présentez ce code au restaurant.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur échange récompense: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'échange'], 500);
        }
    }

    /**
     * Défis du moment
     */
    public function getCurrentChallenges(Request $request)
    {
        $restaurant_id = $request->get('restaurant_id');
        $member_id = $request->get('member_id');

        $challenges = LoyaltyChallenge::where('restaurant_id', $restaurant_id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->map(function($challenge) use ($member_id) {
                $progress = null;
                $completed = false;

                if ($member_id) {
                    $progress = $this->getChallengeProgress($member_id, $challenge->id);
                    $completed = $progress['completed'] ?? false;
                }

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'challenge_type' => $challenge->challenge_type,
                    'target_value' => $challenge->target_value,
                    'reward_points' => $challenge->reward_points,
                    'end_date' => $challenge->end_date,
                    'progress' => $progress,
                    'completed' => $completed,
                ];
            });

        return response()->json(['challenges' => $challenges]);
    }

    /**
     * Tableau de classement
     */
    public function getLeaderboard(Request $request)
    {
        $restaurant_id = $request->get('restaurant_id');
        $period = $request->get('period', 'all_time'); // all_time, month, week

        $query = LoyaltyMember::where('restaurant_id', $restaurant_id)
            ->where('status', 'active');

        // Filtrer par période si nécessaire
        if ($period === 'month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        }

        $leaderboard = $query->orderBy('lifetime_points', 'desc')
            ->take(10)
            ->get()
            ->map(function($member, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $member->name,
                    'points' => $member->lifetime_points,
                    'tier' => $member->tier?->name,
                ];
            });

        return response()->json(['leaderboard' => $leaderboard]);
    }

    /**
     * Calculer les points pour un panier
     */
    public function calculatePointsForCart(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'cart_total' => 'required|numeric|min:0',
            'member_id' => 'nullable|exists:loyalty_members,id',
        ]);

        $restaurant = Restaurant::find($request->restaurant_id);
        $member = $request->member_id ? LoyaltyMember::find($request->member_id) : null;

        $basePoints = $this->loyaltyService->calculatePointsFromAmount(
            $request->cart_total,
            $restaurant
        );

        $multiplier = 1;
        if ($member && $member->tier) {
            $multiplier = $member->tier->points_multiplier ?? 1;
        }

        $totalPoints = round($basePoints * $multiplier);

        return response()->json([
            'cart_total' => $request->cart_total,
            'base_points' => $basePoints,
            'multiplier' => $multiplier,
            'total_points' => $totalPoints,
            'tier_bonus' => $member?->tier?->name,
        ]);
    }

    /**
     * Webhook - Commande complétée
     */
    public function handleOrderCompleted(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            $order = Order::find($request->order_id);

            // Vérifier si le client est membre du programme de fidélité
            $member = null;
            if ($order->customer_email) {
                $member = LoyaltyMember::where('restaurant_id', $order->restaurant_id)
                    ->where('email', $order->customer_email)
                    ->first();
            }

            if ($member) {
                $points = $this->loyaltyService->calculatePointsFromAmount(
                    $order->total_amount,
                    $order->restaurant
                );

                // Appliquer le multiplicateur du niveau
                if ($member->tier) {
                    $points = round($points * ($member->tier->points_multiplier ?? 1));
                }

                $this->loyaltyService->addPoints(
                    $member,
                    $points,
                    'order_purchase',
                    "Commande #{$order->order_number}",
                    ['order_id' => $order->id]
                );

                // Vérifier les défis
                $this->checkChallengeCompletion($member, 'order_count');
                $this->checkChallengeCompletion($member, 'order_amount', $order->total_amount);

                return response()->json([
                    'success' => true,
                    'points_awarded' => $points,
                    'member_balance' => $member->fresh()->points_balance
                ]);
            }

            return response()->json(['message' => 'Client non membre du programme']);

        } catch (\Exception $e) {
            Log::error('Erreur webhook loyalty order completed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur traitement'], 500);
        }
    }

    /**
     * Méthodes utilitaires privées
     */
    private function generateMemberCode($restaurant)
    {
        do {
            $code = $restaurant->code . '-' . strtoupper(Str::random(6));
        } while (LoyaltyMember::where('member_code', $code)->exists());

        return $code;
    }

    private function generateReferralCode()
    {
        do {
            $code = 'REF' . strtoupper(Str::random(8));
        } while (LoyaltyMember::where('referral_code', $code)->exists());

        return $code;
    }

    private function generateRedeemCode()
    {
        do {
            $code = 'RDM' . strtoupper(Str::random(8));
        } while (DB::table('loyalty_redemptions')->where('redeem_code', $code)->exists());

        return $code;
    }

    private function findMember($restaurant_id, $identifier)
    {
        return LoyaltyMember::where('restaurant_id', $restaurant_id)
            ->where(function($query) use ($identifier) {
                $query->where('email', $identifier)
                      ->orWhere('phone', $identifier)
                      ->orWhere('member_code', $identifier);
            })
            ->first();
    }

    private function getBaseTier($restaurant_id)
    {
        return LoyaltyTier::where('restaurant_id', $restaurant_id)
            ->orderBy('min_points')
            ->first();
    }

    private function processReferral($member, $referral_code)
    {
        $referrer = LoyaltyMember::where('referral_code', $referral_code)
            ->where('restaurant_id', $member->restaurant_id)
            ->first();

        if ($referrer) {
            // Points pour le parrain
            $referralBonus = $member->restaurant->loyalty_referral_bonus ?? 50;

            $this->loyaltyService->addPoints(
                $referrer,
                $referralBonus,
                'referral_bonus',
                "Parrainage de {$member->name}"
            );

            // Enregistrer la relation de parrainage
            DB::table('loyalty_referrals')->insert([
                'referrer_id' => $referrer->id,
                'referred_id' => $member->id,
                'bonus_points' => $referralBonus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getTransactionTypeLabel($type)
    {
        return match($type) {
            'welcome_bonus' => 'Bonus de bienvenue',
            'order_purchase' => 'Achat',
            'reward_redemption' => 'Échange récompense',
            'referral_bonus' => 'Bonus parrainage',
            'birthday_bonus' => 'Bonus anniversaire',
            'challenge_completion' => 'Défi complété',
            'admin_adjustment' => 'Ajustement manuel',
            default => 'Transaction',
        };
    }

    private function getNextTierPoints($member)
    {
        if (!$member->tier) return null;

        $nextTier = LoyaltyTier::where('restaurant_id', $member->restaurant_id)
            ->where('min_points', '>', $member->tier->min_points)
            ->orderBy('min_points')
            ->first();

        return $nextTier ? ($nextTier->min_points - $member->lifetime_points) : null;
    }

    private function getPointsToExpire($member)
    {
        // Points qui expirent dans les 30 prochains jours
        $expirationDate = now()->addDays(30);

        return LoyaltyTransaction::where('member_id', $member->id)
            ->where('type', 'like', '%_bonus')
            ->where('expires_at', '<=', $expirationDate)
            ->where('expires_at', '>', now())
            ->sum('points');
    }

    private function getChallengeProgress($member_id, $challenge_id)
    {
        // Logique pour calculer le progrès d'un défi
        // À implémenter selon le type de défi
        return ['current' => 0, 'target' => 1, 'completed' => false];
    }

    private function checkChallengeCompletion($member, $type, $value = null)
    {
        // Vérifier et compléter les défis automatiquement
        // À implémenter selon la logique métier
    }
}
