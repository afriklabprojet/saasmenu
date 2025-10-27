<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableQrCode;
use App\Http\Requests\Api\ScanQrCodeRequest;
use App\Http\Requests\Api\CreateTableQrRequest;
use Illuminate\Http\Request;

class TableQrApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tableqr/tables",
     *     summary="Get table QR codes",
     *     description="Retrieve all table QR codes for the authenticated restaurant",
     *     tags={"Table QR"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by table status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filter by table location",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of table QR codes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tables retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/TableQrCode")
     *             ),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getTables(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');

        $query = TableQrCode::where('restaurant_id', $restaurantId);

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $tables = $query->orderBy('table_number')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Tables retrieved successfully',
            'data' => $tables->items(),
            'meta' => [
                'current_page' => $tables->currentPage(),
                'last_page' => $tables->lastPage(),
                'per_page' => $tables->perPage(),
                'total' => $tables->total(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tableqr/tables/{tableId}",
     *     summary="Get table QR code details",
     *     description="Retrieve details of a specific table QR code",
     *     tags={"Table QR"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="tableId",
     *         in="path",
     *         description="Table QR code ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table QR code details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TableQrCode")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Table not found")
     * )
     */
    public function getTable($tableId)
    {
        $table = TableQrCode::findOrFail($tableId);

        return response()->json([
            'success' => true,
            'data' => $table,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tableqr/tables",
     *     summary="Create table QR code",
     *     description="Create a new table QR code",
     *     tags={"Table QR"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="table_number", type="integer", example=15),
     *             @OA\Property(property="table_name", type="string", example="VIP Table 15"),
     *             @OA\Property(property="capacity", type="integer", example=6),
     *             @OA\Property(property="location", type="string", example="Terrace"),
     *             @OA\Property(property="notes", type="string", example="Window side with city view")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Table QR code created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Table QR code created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TableQrCode")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=409, description="Table number already exists")
     * )
     */
    public function createTable(CreateTableQrRequest $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');

        // Check if table number already exists
        $existingTable = TableQrCode::where('restaurant_id', $restaurantId)
            ->where('table_number', $request->table_number)
            ->first();

        if ($existingTable) {
            return response()->json([
                'success' => false,
                'message' => 'Table number already exists',
            ], 409);
        }

        $qrCode = $this->generateQrCode($restaurantId, $request->table_number);

        $table = TableQrCode::create([
            'restaurant_id' => $restaurantId,
            'table_number' => $request->table_number,
            'table_name' => $request->table_name ?? "Table {$request->table_number}",
            'qr_code' => $qrCode,
            'url' => url("/menu/table/{$qrCode}"),
            'capacity' => $request->capacity ?? 4,
            'location' => $request->location ?? 'Main Hall',
            'notes' => $request->notes,
            'is_active' => true,
            'scan_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Table QR code created successfully',
            'data' => $table->fresh(),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/tableqr/scan",
     *     summary="Scan table QR code",
     *     description="Process a table QR code scan and update analytics",
     *     tags={"Table QR"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="qr_code", type="string", example="QR-1234-ABCD-5678"),
     *             @OA\Property(property="customer_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="session_id", type="string", example="sess_abc123"),
     *             @OA\Property(property="user_agent", type="string", example="Mozilla/5.0...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code scanned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="QR code scanned successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="restaurant_id", type="integer", example=1),
     *                 @OA\Property(property="table_number", type="integer", example=5),
     *                 @OA\Property(property="table_name", type="string", example="Table 5"),
     *                 @OA\Property(property="capacity", type="integer", example=4),
     *                 @OA\Property(property="menu_url", type="string", example="https://api.restro-saas.com/menu/1"),
     *                 @OA\Property(property="scan_id", type="string", example="scan_xyz789")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="QR code not found"),
     *     @OA\Response(response=410, description="Table inactive")
     * )
     */
    public function scanQrCode(ScanQrCodeRequest $request)
    {
        $table = TableQrCode::where('qr_code', $request->qr_code)->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not found',
            ], 404);
        }

        if (!$table->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This table is currently inactive',
            ], 410);
        }

        // Update scan analytics
        $table->increment('scan_count');
        $table->update(['last_scanned_at' => now()]);

        // Generate scan ID for tracking
        $scanId = 'scan_' . uniqid();

        return response()->json([
            'success' => true,
            'message' => 'QR code scanned successfully',
            'data' => [
                'restaurant_id' => $table->restaurant_id,
                'table_number' => $table->table_number,
                'table_name' => $table->table_name,
                'capacity' => $table->capacity,
                'location' => $table->location,
                'menu_url' => url("/api/menu/restaurant/{$table->restaurant_id}"),
                'scan_id' => $scanId,
                'scanned_at' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tableqr/analytics",
     *     summary="Get QR scan analytics",
     *     description="Retrieve QR code scan analytics for the restaurant",
     *     tags={"Table QR"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Analytics period",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month", "year"}, default="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR analytics data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_scans", type="integer", example=1250),
     *                 @OA\Property(property="unique_tables_scanned", type="integer", example=25),
     *                 @OA\Property(property="most_popular_table", type="integer", example=7),
     *                 @OA\Property(property="average_scans_per_table", type="number", format="float", example=50.0),
     *                 @OA\Property(
     *                     property="daily_scans",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="date", type="string", format="date"),
     *                         @OA\Property(property="scans", type="integer")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="top_tables",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="table_number", type="integer"),
     *                         @OA\Property(property="table_name", type="string"),
     *                         @OA\Property(property="scan_count", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAnalytics(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');
        $period = $request->get('period', 'month');

        // Calculate date range based on period
        $dateFrom = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $tables = TableQrCode::where('restaurant_id', $restaurantId)->get();

        $totalScans = $tables->sum('scan_count');
        $uniqueTablesScanned = $tables->where('scan_count', '>', 0)->count();
        $mostPopularTable = $tables->sortByDesc('scan_count')->first();
        $averageScansPerTable = $uniqueTablesScanned > 0 ? $totalScans / $uniqueTablesScanned : 0;

        // Top 10 tables by scan count
        $topTables = $tables->sortByDesc('scan_count')
            ->take(10)
            ->map(function ($table) {
                return [
                    'table_number' => $table->table_number,
                    'table_name' => $table->table_name,
                    'scan_count' => $table->scan_count,
                    'location' => $table->location,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'unique_tables_scanned' => $uniqueTablesScanned,
                'most_popular_table' => $mostPopularTable?->table_number,
                'average_scans_per_table' => round($averageScansPerTable, 1),
                'period' => $period,
                'date_from' => $dateFrom->toDateString(),
                'top_tables' => $topTables,
                'active_tables' => $tables->where('is_active', true)->count(),
                'inactive_tables' => $tables->where('is_active', false)->count(),
            ]
        ]);
    }

    private function generateQrCode($restaurantId, $tableNumber): string
    {
        return 'QR-' . $restaurantId . '-' . str_pad($tableNumber, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));
    }
}

/**
 * @OA\Schema(
 *     schema="TableQrCode",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="table_number", type="integer", example=5),
 *     @OA\Property(property="table_name", type="string", example="Table 5"),
 *     @OA\Property(property="qr_code", type="string", example="QR-1234-ABCD-5678"),
 *     @OA\Property(property="url", type="string", example="https://api.restro-saas.com/menu/table/QR-1234-ABCD-5678"),
 *     @OA\Property(property="capacity", type="integer", example=4),
 *     @OA\Property(property="location", type="string", example="Main Hall"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="scan_count", type="integer", example=127),
 *     @OA\Property(property="last_scanned_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="notes", type="string", example="Window side table", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
