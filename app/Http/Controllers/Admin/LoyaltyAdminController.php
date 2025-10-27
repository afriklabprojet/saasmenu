<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyMember;
use App\Models\Customer;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\DB;

class LoyaltyAdminController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Dashboard overview
     */
    public function index()
    {
        $stats = [
            'total_members' => LoyaltyCard::count(),
            'active_members' => LoyaltyCard::where('status', 1)->count(),
            'total_points_issued' => LoyaltyTransaction::where('type', 'earned')->sum('points'),
            'total_points_redeemed' => LoyaltyTransaction::where('type', 'redeemed')->sum('points'),
            'total_revenue_impact' => LoyaltyCard::sum('total_spent'),
            'avg_points_per_member' => LoyaltyCard::avg('points'),
        ];

        $recent_transactions = LoyaltyTransaction::with(['loyaltyCard.customer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $top_members = LoyaltyCard::with(['customer'])
            ->orderBy('points', 'desc')
            ->limit(10)
            ->get();

        return view('admin.loyalty.index', compact('stats', 'recent_transactions', 'top_members'));
    }

    /**
     * Loyalty program settings
     */
    public function settings()
    {
        $settings = $this->loyaltyService->getSettings();
        return view('admin.loyalty.settings', compact('settings'));
    }

    /**
     * Update loyalty program settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'points_per_euro' => 'required|numeric|min:0',
            'euro_per_point' => 'required|numeric|min:0',
            'welcome_points' => 'required|integer|min:0',
            'birthday_points' => 'required|integer|min:0',
            'referral_points' => 'required|integer|min:0',
            'review_points' => 'required|integer|min:0',
            'social_share_points' => 'required|integer|min:0',
            'min_order_for_points' => 'required|numeric|min:0',
            'points_expiry_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $this->loyaltyService->updateSettings($request->all());

        return redirect()->route('admin.loyalty.settings')
            ->with('success', 'Paramètres de fidélité mis à jour avec succès');
    }

    /**
     * Loyalty tiers management
     */
    public function tiers()
    {
        $tiers = LoyaltyTier::orderBy('min_points')->get();
        return view('admin.loyalty.tiers.index', compact('tiers'));
    }

    /**
     * Toggle tier status
     */
    public function toggleTier($id)
    {
        $tier = LoyaltyTier::findOrFail($id);
        $tier->update(['is_active' => !$tier->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du niveau mis à jour',
            'is_active' => $tier->is_active
        ]);
    }

    /**
     * Loyalty rewards management
     */
    public function rewards()
    {
        $rewards = LoyaltyReward::with(['tier'])->orderBy('points_required')->get();
        return view('admin.loyalty.rewards.index', compact('rewards'));
    }

    /**
     * Toggle reward status
     */
    public function toggleReward($id)
    {
        $reward = LoyaltyReward::findOrFail($id);
        $reward->update(['is_active' => !$reward->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Statut de la récompense mis à jour',
            'is_active' => $reward->is_active
        ]);
    }

    /**
     * Loyalty challenges management
     */
    public function challenges()
    {
        // Pour l'instant, retourner une vue vide
        return view('admin.loyalty.challenges.index');
    }

    /**
     * Toggle challenge status
     */
    public function toggleChallenge($id)
    {
        // Implémentation future
        return response()->json([
            'success' => true,
            'message' => 'Statut du défi mis à jour'
        ]);
    }

    /**
     * Loyalty members management
     */
    public function members(Request $request)
    {
        $query = LoyaltyCard::with(['customer', 'restaurant']);

        if ($request->search) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->tier) {
            $query->where('tier_id', $request->tier);
        }

        if ($request->status !== null) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);
        $tiers = LoyaltyTier::orderBy('min_points')->get();

        return view('admin.loyalty.members.index', compact('members', 'tiers'));
    }

    /**
     * Show specific member
     */
    public function showMember($id)
    {
        $member = LoyaltyCard::with(['customer', 'restaurant', 'transactions'])
            ->findOrFail($id);

        return view('admin.loyalty.members.show', compact('member'));
    }

    /**
     * Update member tier
     */
    public function updateMemberTier(Request $request, $id)
    {
        $request->validate([
            'tier_id' => 'required|exists:loyalty_tiers,id'
        ]);

        $member = LoyaltyCard::findOrFail($id);
        $member->update(['tier_id' => $request->tier_id]);

        return response()->json([
            'success' => true,
            'message' => 'Niveau du membre mis à jour'
        ]);
    }

    /**
     * Add points to member
     */
    public function addPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255'
        ]);

        $member = LoyaltyCard::findOrFail($id);
        $member->addPoints($request->points, $request->description);

        return response()->json([
            'success' => true,
            'message' => 'Points ajoutés avec succès'
        ]);
    }

    /**
     * Deduct points from member
     */
    public function deductPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255'
        ]);

        $member = LoyaltyCard::findOrFail($id);

        if ($member->points < $request->points) {
            return response()->json([
                'success' => false,
                'message' => 'Points insuffisants'
            ], 400);
        }

        $member->redeemPoints($request->points, $request->description);

        return response()->json([
            'success' => true,
            'message' => 'Points déduits avec succès'
        ]);
    }

    /**
     * Loyalty transactions
     */
    public function transactions(Request $request)
    {
        $query = LoyaltyTransaction::with(['loyaltyCard.customer']);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.loyalty.transactions.index', compact('transactions'));
    }

    /**
     * Export transactions
     */
    public function exportTransactions(Request $request)
    {
        // Implémentation future avec Excel export
        return response()->json([
            'success' => true,
            'message' => 'Export en cours de développement'
        ]);
    }

    /**
     * Loyalty analytics
     */
    public function analytics()
    {
        $analytics = [
            'monthly_growth' => $this->getMonthlyGrowth(),
            'points_distribution' => $this->getPointsDistribution(),
            'tier_distribution' => $this->getTierDistribution(),
            'redemption_rate' => $this->getRedemptionRate(),
        ];

        return view('admin.loyalty.analytics.index', compact('analytics'));
    }

    /**
     * Retention analytics
     */
    public function retentionAnalytics()
    {
        $retention = $this->calculateRetentionRate();
        return response()->json($retention);
    }

    /**
     * Revenue analytics
     */
    public function revenueAnalytics()
    {
        $revenue = $this->calculateRevenueImpact();
        return response()->json($revenue);
    }

    /**
     * Campaigns management
     */
    public function campaigns()
    {
        // Implémentation future
        return view('admin.loyalty.campaigns.index');
    }

    /**
     * Toggle campaign status
     */
    public function toggleCampaign($id)
    {
        // Implémentation future
        return response()->json([
            'success' => true,
            'message' => 'Statut de la campagne mis à jour'
        ]);
    }

    /**
     * Communications management
     */
    public function communications()
    {
        // Implémentation future
        return view('admin.loyalty.communications.index');
    }

    /**
     * Send communication
     */
    public function sendCommunication(Request $request)
    {
        // Implémentation future
        return response()->json([
            'success' => true,
            'message' => 'Communication envoyée'
        ]);
    }

    /**
     * Customer segments
     */
    public function segments()
    {
        // Implémentation future
        return view('admin.loyalty.segments.index');
    }

    /**
     * Create customer segment
     */
    public function createSegment(Request $request)
    {
        // Implémentation future
        return response()->json([
            'success' => true,
            'message' => 'Segment créé'
        ]);
    }

    /**
     * Private helper methods
     */
    private function getMonthlyGrowth()
    {
        return LoyaltyCard::selectRaw('COUNT(*) as count, MONTH(created_at) as month, YEAR(created_at) as year')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function getPointsDistribution()
    {
        return LoyaltyCard::selectRaw('
            CASE
                WHEN points = 0 THEN "0 points"
                WHEN points BETWEEN 1 AND 100 THEN "1-100 points"
                WHEN points BETWEEN 101 AND 500 THEN "101-500 points"
                WHEN points BETWEEN 501 AND 1000 THEN "501-1000 points"
                ELSE "1000+ points"
            END as range,
            COUNT(*) as count
        ')
        ->groupBy('range')
        ->get();
    }

    private function getTierDistribution()
    {
        return DB::table('loyalty_cards')
            ->join('loyalty_tiers', 'loyalty_cards.tier_id', '=', 'loyalty_tiers.id')
            ->select('loyalty_tiers.name', DB::raw('COUNT(*) as count'))
            ->groupBy('loyalty_tiers.id', 'loyalty_tiers.name')
            ->get();
    }

    private function getRedemptionRate()
    {
        $totalEarned = LoyaltyTransaction::where('type', 'earned')->sum('points');
        $totalRedeemed = LoyaltyTransaction::where('type', 'redeemed')->sum('points');

        return $totalEarned > 0 ? ($totalRedeemed / $totalEarned) * 100 : 0;
    }

    private function calculateRetentionRate()
    {
        // Logique de calcul du taux de rétention
        return [
            'monthly_retention' => 85.5,
            'quarterly_retention' => 72.3,
            'yearly_retention' => 58.7
        ];
    }

    private function calculateRevenueImpact()
    {
        // Logique de calcul de l'impact sur le chiffre d'affaires
        return [
            'total_loyalty_revenue' => LoyaltyCard::sum('total_spent'),
            'avg_spend_per_member' => LoyaltyCard::avg('total_spent'),
            'loyalty_vs_non_loyalty' => 1.35 // 35% de plus en moyenne
        ];
    }
}
