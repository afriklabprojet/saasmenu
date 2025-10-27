<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTransaction;
use App\Http\Requests\Api\CreateLoyaltyMemberRequest;
use App\Http\Requests\Api\LoyaltyTransactionRequest;
use Illuminate\Http\Request;

class LoyaltyApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/loyalty/programs",
     *     summary="Get loyalty programs",
     *     description="Retrieve all loyalty programs for the authenticated restaurant",
     *     tags={"Loyalty Program"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by program status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of loyalty programs",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Programs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/LoyaltyProgram")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getPrograms(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');

        $query = LoyaltyProgram::where('restaurant_id', $restaurantId);

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $programs = $query->with(['members' => function($q) {
            $q->selectRaw('program_id, COUNT(*) as member_count')
              ->groupBy('program_id');
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Programs retrieved successfully',
            'data' => $programs,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/loyalty/programs/{programId}/members",
     *     summary="Enroll customer in loyalty program",
     *     description="Add a new member to the loyalty program",
     *     tags={"Loyalty Program"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="programId",
     *         in="path",
     *         description="Loyalty program ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="email", type="string", example="customer@example.com"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="birthday", type="string", format="date", example="1990-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Member enrolled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Member enrolled successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LoyaltyMember")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=409, description="Member already exists")
     * )
     */
    public function enrollMember($programId, CreateLoyaltyMemberRequest $request)
    {
        $program = LoyaltyProgram::findOrFail($programId);

        // Check if member already exists
        $existingMember = LoyaltyMember::where('program_id', $programId)
            ->where(function($query) use ($request) {
                if ($request->user_id) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->phone) {
                    $query->orWhere('phone', $request->phone);
                }
                if ($request->email) {
                    $query->orWhere('email', $request->email);
                }
            })->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'Member already enrolled in this program',
            ], 409);
        }

        $member = LoyaltyMember::create([
            'program_id' => $programId,
            'restaurant_id' => $program->restaurant_id,
            'user_id' => $request->user_id,
            'member_number' => $this->generateMemberNumber($program->restaurant_id),
            'phone' => $request->phone,
            'email' => $request->email,
            'name' => $request->name,
            'birthday' => $request->birthday,
            'points_balance' => 0,
            'total_earned' => 0,
            'total_redeemed' => 0,
            'tier_level' => 'Bronze',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member enrolled successfully',
            'data' => $member->fresh(),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/members/{memberId}",
     *     summary="Get loyalty member details",
     *     description="Retrieve details of a specific loyalty member",
     *     tags={"Loyalty Program"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="memberId",
     *         in="path",
     *         description="Loyalty member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/LoyaltyMember")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Member not found")
     * )
     */
    public function getMember($memberId)
    {
        $member = LoyaltyMember::with(['program', 'recentTransactions' => function($query) {
            $query->latest()->take(10);
        }])->findOrFail($memberId);

        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/loyalty/members/{memberId}/transactions",
     *     summary="Create loyalty transaction",
     *     description="Award or redeem loyalty points for a member",
     *     tags={"Loyalty Program"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="memberId",
     *         in="path",
     *         description="Loyalty member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"earn", "redeem", "bonus", "adjustment"}, example="earn"),
     *             @OA\Property(property="points", type="integer", example=50),
     *             @OA\Property(property="description", type="string", example="Purchase reward"),
     *             @OA\Property(property="order_id", type="string", example="ORD-123456"),
     *             @OA\Property(property="reference_type", type="string", example="order"),
     *             @OA\Property(property="reference_id", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Points awarded successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LoyaltyTransaction")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Insufficient points for redemption")
     * )
     */
    public function createTransaction($memberId, LoyaltyTransactionRequest $request)
    {
        $member = LoyaltyMember::findOrFail($memberId);

        // Check if member has sufficient points for redemption
        if ($request->type === 'redeem' && $member->points_balance < $request->points) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points balance',
                'available_points' => $member->points_balance,
                'required_points' => $request->points,
            ], 400);
        }

        $transaction = LoyaltyTransaction::create([
            'program_id' => $member->program_id,
            'member_id' => $memberId,
            'restaurant_id' => $member->restaurant_id,
            'user_id' => $member->user_id,
            'type' => $request->type,
            'points' => $request->points,
            'description' => $request->description,
            'order_id' => $request->order_id,
            'reference_type' => $request->reference_type,
            'reference_id' => $request->reference_id,
            'processed_at' => now(),
        ]);

        // Update member points balance
        $pointsChange = in_array($request->type, ['earn', 'bonus', 'adjustment'])
            ? $request->points
            : -$request->points;

        $member->increment('points_balance', $pointsChange);

        if ($pointsChange > 0) {
            $member->increment('total_earned', $request->points);
        } else {
            $member->increment('total_redeemed', $request->points);
        }

        // Update tier level based on total earned points
        $this->updateMemberTier($member);

        $message = $request->type === 'redeem'
            ? 'Points redeemed successfully'
            : 'Points awarded successfully';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $transaction->fresh()->load('member'),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/loyalty/members/{memberId}/transactions",
     *     summary="Get member transaction history",
     *     description="Retrieve transaction history for a loyalty member",
     *     tags={"Loyalty Program"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="memberId",
     *         in="path",
     *         description="Loyalty member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by transaction type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"earn", "redeem", "bonus", "adjustment"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/LoyaltyTransaction")
     *             ),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     )
     * )
     */
    public function getTransactions($memberId, Request $request)
    {
        $member = LoyaltyMember::findOrFail($memberId);

        $query = LoyaltyTransaction::where('member_id', $memberId)
            ->with(['member', 'program']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest('processed_at')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    private function generateMemberNumber($restaurantId): string
    {
        $prefix = 'LM' . $restaurantId;
        $sequence = LoyaltyMember::where('restaurant_id', $restaurantId)->count() + 1;
        return $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    private function updateMemberTier(LoyaltyMember $member): void
    {
        $totalEarned = $member->total_earned;

        if ($totalEarned >= 5000) {
            $tier = 'Platinum';
        } elseif ($totalEarned >= 2000) {
            $tier = 'Gold';
        } elseif ($totalEarned >= 500) {
            $tier = 'Silver';
        } else {
            $tier = 'Bronze';
        }

        if ($member->tier_level !== $tier) {
            $member->update(['tier_level' => $tier]);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="LoyaltyProgram",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="VIP Rewards Program"),
 *     @OA\Property(property="description", type="string", example="Earn points with every purchase"),
 *     @OA\Property(property="type", type="string", enum={"points", "visits", "tier"}, example="points"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="rules",
 *         type="object",
 *         @OA\Property(property="points_per_dollar", type="number", format="float", example=1.5),
 *         @OA\Property(property="min_points_redeem", type="integer", example=100),
 *         @OA\Property(property="max_points_redeem", type="integer", example=1000)
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="LoyaltyMember",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="member_number", type="string", example="LM1000001"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="points_balance", type="integer", example=250),
 *     @OA\Property(property="total_earned", type="integer", example=1500),
 *     @OA\Property(property="total_redeemed", type="integer", example=1250),
 *     @OA\Property(property="tier_level", type="string", enum={"Bronze", "Silver", "Gold", "Platinum"}, example="Silver"),
 *     @OA\Property(property="birthday", type="string", format="date", example="1990-01-15", nullable=true),
 *     @OA\Property(property="joined_at", type="string", format="date-time"),
 *     @OA\Property(property="last_activity_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LoyaltyTransaction",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="program_id", type="integer", example=1),
 *     @OA\Property(property="member_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", enum={"earn", "redeem", "bonus", "adjustment"}, example="earn"),
 *     @OA\Property(property="points", type="integer", example=50),
 *     @OA\Property(property="description", type="string", example="Purchase reward"),
 *     @OA\Property(property="order_id", type="string", example="ORD-123456", nullable=true),
 *     @OA\Property(property="reference_type", type="string", example="order", nullable=true),
 *     @OA\Property(property="reference_id", type="integer", example=123, nullable=true),
 *     @OA\Property(property="processed_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
