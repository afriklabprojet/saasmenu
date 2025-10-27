<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\POSTerminal;
use App\Models\POSSession;
use App\Models\POSCart;
use App\Models\User;
use App\Models\Order;
use App\Services\POSService;
use Illuminate\Support\Facades\DB;

class POSAdminController extends Controller
{
    protected $posService;

    public function __construct(POSService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * POS settings
     */
    public function settings()
    {
        $settings = $this->posService->getSettings();
        return view('admin.pos.settings', compact('settings'));
    }

    /**
     * Update POS settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'service_charge' => 'required|numeric|min:0',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
            'auto_print_receipt' => 'boolean',
            'allow_discount' => 'boolean',
            'require_customer_info' => 'boolean',
            'default_payment_method' => 'required|string',
            'currency_symbol' => 'required|string|max:5',
            'currency_position' => 'required|in:before,after',
        ]);

        $this->posService->updateSettings($request->all());

        return redirect()->route('admin.pos.settings')
            ->with('success', 'Paramètres POS mis à jour avec succès');
    }

    /**
     * POS terminals management
     */
    public function terminals()
    {
        $terminals = POSTerminal::with(['user', 'sessions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pos.terminals.index', compact('terminals'));
    }

    /**
     * Toggle terminal status
     */
    public function toggleTerminal($id)
    {
        $terminal = POSTerminal::findOrFail($id);
        $terminal->update(['is_active' => !$terminal->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du terminal mis à jour',
            'is_active' => $terminal->is_active
        ]);
    }

    /**
     * Sync terminal data
     */
    public function syncTerminal($id)
    {
        $terminal = POSTerminal::findOrFail($id);

        // Implémentation de la synchronisation
        $this->posService->syncTerminal($terminal);

        return response()->json([
            'success' => true,
            'message' => 'Terminal synchronisé avec succès'
        ]);
    }

    /**
     * POS users management
     */
    public function users()
    {
        $users = User::where('pos_access', true)
            ->with(['posTerminals', 'posSessions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pos.users.index', compact('users'));
    }

    /**
     * Create POS user
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'pos_permissions' => 'required|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'pos_access' => true,
            'pos_permissions' => $request->pos_permissions,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur POS créé avec succès',
            'user' => $user
        ]);
    }

    /**
     * Update POS user
     */
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'pos_permissions' => 'required|array',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'pos_permissions' => $request->pos_permissions,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur POS mis à jour',
            'user' => $user
        ]);
    }

    /**
     * Update user permissions
     */
    public function updateUserPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        $user = User::findOrFail($id);
        $user->update(['pos_permissions' => $request->permissions]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions mises à jour'
        ]);
    }

    /**
     * POS sessions management
     */
    public function sessions(Request $request)
    {
        $query = POSSession::with(['user', 'terminal']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pos.sessions.index', compact('sessions'));
    }

    /**
     * Show specific session
     */
    public function showSession($id)
    {
        $session = POSSession::with(['user', 'terminal', 'orders'])
            ->findOrFail($id);

        return view('admin.pos.sessions.show', compact('session'));
    }

    /**
     * Reconcile session
     */
    public function reconcileSession(Request $request, $id)
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $session = POSSession::findOrFail($id);

        $difference = $request->actual_cash - $session->expected_cash;

        $session->update([
            'actual_cash' => $request->actual_cash,
            'cash_difference' => $difference,
            'reconciliation_notes' => $request->notes,
            'reconciled_at' => now(),
            'status' => 'reconciled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session réconciliée avec succès',
            'difference' => $difference
        ]);
    }

    /**
     * POS analytics
     */
    public function analytics()
    {
        $analytics = [
            'daily_sales' => $this->getDailySales(),
            'hourly_distribution' => $this->getHourlyDistribution(),
            'payment_methods' => $this->getPaymentMethodsStats(),
            'top_items' => $this->getTopItems(),
            'user_performance' => $this->getUserPerformance(),
        ];

        return view('admin.pos.analytics.index', compact('analytics'));
    }

    /**
     * Sales analytics
     */
    public function salesAnalytics()
    {
        $sales = [
            'today' => $this->getTodaySales(),
            'week' => $this->getWeekSales(),
            'month' => $this->getMonthSales(),
            'year' => $this->getYearSales(),
        ];

        return response()->json($sales);
    }

    /**
     * Performance analytics
     */
    public function performanceAnalytics()
    {
        $performance = [
            'avg_transaction_time' => $this->getAvgTransactionTime(),
            'orders_per_hour' => $this->getOrdersPerHour(),
            'peak_hours' => $this->getPeakHours(),
            'efficiency_metrics' => $this->getEfficiencyMetrics(),
        ];

        return response()->json($performance);
    }

    /**
     * Device management
     */
    public function devices()
    {
        $devices = [
            'printers' => $this->getConnectedPrinters(),
            'scanners' => $this->getConnectedScanners(),
            'cash_drawers' => $this->getCashDrawers(),
        ];

        return view('admin.pos.devices.index', compact('devices'));
    }

    /**
     * Test printer
     */
    public function testPrinter(Request $request)
    {
        $request->validate([
            'printer_id' => 'required|string'
        ]);

        try {
            $this->posService->testPrinter($request->printer_id);

            return response()->json([
                'success' => true,
                'message' => 'Test d\'impression réussi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test d\'impression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function getDailySales()
    {
        return Order::whereDate('created_at', today())
            ->sum('total');
    }

    private function getHourlyDistribution()
    {
        return Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(total) as revenue')
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    private function getPaymentMethodsStats()
    {
        return Order::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->whereDate('created_at', today())
            ->groupBy('payment_method')
            ->get();
    }

    private function getTopItems()
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->select('items.item_name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereDate('orders.created_at', today())
            ->groupBy('items.id', 'items.item_name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    private function getUserPerformance()
    {
        return User::join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.name', DB::raw('COUNT(orders.id) as order_count'), DB::raw('SUM(orders.total) as total_sales'))
            ->whereDate('orders.created_at', today())
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_sales', 'desc')
            ->get();
    }

    private function getTodaySales()
    {
        return Order::whereDate('created_at', today())->sum('total');
    }

    private function getWeekSales()
    {
        return Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
    }

    private function getMonthSales()
    {
        return Order::whereMonth('created_at', now()->month)->sum('total');
    }

    private function getYearSales()
    {
        return Order::whereYear('created_at', now()->year)->sum('total');
    }

    private function getAvgTransactionTime()
    {
        // Implémentation future
        return 45; // secondes
    }

    private function getOrdersPerHour()
    {
        return Order::whereDate('created_at', today())->count() / 24;
    }

    private function getPeakHours()
    {
        return Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();
    }

    private function getEfficiencyMetrics()
    {
        return [
            'transactions_per_minute' => 2.5,
            'error_rate' => 1.2,
            'customer_wait_time' => 3.5
        ];
    }

    private function getConnectedPrinters()
    {
        // Implémentation future
        return [];
    }

    private function getConnectedScanners()
    {
        // Implémentation future
        return [];
    }

    private function getCashDrawers()
    {
        // Implémentation future
        return [];
    }
}
