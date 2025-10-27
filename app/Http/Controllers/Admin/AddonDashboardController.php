<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\POSSession;
use App\Models\POSTerminal;
use App\Models\LoyaltyMember;
use App\Models\ImportJob;
use App\Models\ExportJob;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddonDashboardController extends Controller
{
    /**
     * Dashboard principal des addons
     */
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Statistiques générales
        $stats = $this->getDashboardStats($restaurant);

        // Données pour les graphiques
        $chartsData = $this->getChartsData($restaurant);

        // Activité récente
        $recentActivity = $this->getRecentActivity($restaurant);

        return view('admin.addons.dashboard', compact('stats', 'chartsData', 'recentActivity'));
    }

    /**
     * Dashboard POS
     */
    public function pos(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Statistiques POS
        $posStats = [
            'active_terminals' => POSTerminal::where('restaurant_id', $restaurant->id)
                ->where('status', 'active')->count(),
            'active_sessions' => POSSession::where('restaurant_id', $restaurant->id)
                ->where('status', 'active')->count(),
            'today_sales' => Order::where('restaurant_id', $restaurant->id)
                ->where('order_source', 'pos')
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'today_orders' => Order::where('restaurant_id', $restaurant->id)
                ->where('order_source', 'pos')
                ->whereDate('created_at', today())
                ->count()
        ];

        // Terminaux et leurs statuts
        $terminals = POSTerminal::where('restaurant_id', $restaurant->id)
            ->with(['currentUser', 'activeSession'])
            ->get();

        // Sessions récentes
        $recentSessions = POSSession::where('restaurant_id', $restaurant->id)
            ->with(['user', 'terminal'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.addons.pos', compact('posStats', 'terminals', 'recentSessions'));
    }

    /**
     * Dashboard Programme de Fidélité
     */
    public function loyalty(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Statistiques fidélité
        $loyaltyStats = [
            'total_members' => LoyaltyMember::where('restaurant_id', $restaurant->id)->count(),
            'active_members' => LoyaltyMember::where('restaurant_id', $restaurant->id)
                ->where('status', 'active')->count(),
            'total_points_distributed' => LoyaltyMember::where('restaurant_id', $restaurant->id)
                ->sum('lifetime_points'),
            'points_redeemed_today' => 0 // À calculer avec les transactions
        ];

        // Nouveaux membres par mois
        $newMembersChart = LoyaltyMember::where('restaurant_id', $restaurant->id)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->get();

        // Top membres
        $topMembers = LoyaltyMember::where('restaurant_id', $restaurant->id)
            ->orderBy('points_balance', 'desc')
            ->take(10)
            ->get();

        return view('admin.addons.loyalty', compact('loyaltyStats', 'newMembersChart', 'topMembers'));
    }

    /**
     * Dashboard Import/Export
     */
    public function importExport(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Statistiques import/export
        $importExportStats = [
            'total_imports' => ImportJob::where('restaurant_id', $restaurant->id)->count(),
            'successful_imports' => ImportJob::where('restaurant_id', $restaurant->id)
                ->where('status', 'completed')->count(),
            'total_exports' => ExportJob::where('restaurant_id', $restaurant->id)->count(),
            'pending_jobs' => ImportJob::where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count() + ExportJob::where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count()
        ];

        // Jobs récents
        $recentJobs = collect()
            ->merge(ImportJob::where('restaurant_id', $restaurant->id)->latest()->take(5)->get())
            ->merge(ExportJob::where('restaurant_id', $restaurant->id)->latest()->take(5)->get())
            ->sortByDesc('created_at')
            ->take(10);

        return view('admin.addons.import-export', compact('importExportStats', 'recentJobs'));
    }

    /**
     * Dashboard Firebase Notifications
     */
    public function notifications(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Statistiques notifications
        $notificationStats = [
            'total_notifications' => Notification::where('restaurant_id', $restaurant->id)->count(),
            'sent_notifications' => Notification::where('restaurant_id', $restaurant->id)
                ->where('status', 'sent')->count(),
            'scheduled_notifications' => Notification::where('restaurant_id', $restaurant->id)
                ->where('status', 'scheduled')->count(),
            'total_recipients' => Notification::where('restaurant_id', $restaurant->id)
                ->sum('sent_count')
        ];

        // Notifications récentes
        $recentNotifications = Notification::where('restaurant_id', $restaurant->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.addons.notifications', compact('notificationStats', 'recentNotifications'));
    }

    /**
     * Obtenir les statistiques du dashboard
     */
    private function getDashboardStats($restaurant)
    {
        return [
            'total_orders' => Order::where('restaurant_id', $restaurant->id)->count(),
            'total_customers' => Customer::where('restaurant_id', $restaurant->id)->count(),
            'total_menu_items' => MenuItem::where('restaurant_id', $restaurant->id)->count(),
            'loyalty_members' => LoyaltyMember::where('restaurant_id', $restaurant->id)->count(),
            'pos_terminals' => POSTerminal::where('restaurant_id', $restaurant->id)->count(),
            'total_revenue' => Order::where('restaurant_id', $restaurant->id)->sum('total_amount'),
            'today_revenue' => Order::where('restaurant_id', $restaurant->id)
                ->whereDate('created_at', today())->sum('total_amount'),
            'this_month_revenue' => Order::where('restaurant_id', $restaurant->id)
                ->whereMonth('created_at', now()->month)->sum('total_amount'),
        ];
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartsData($restaurant)
    {
        // Revenus des 7 derniers jours
        $dailyRevenue = Order::where('restaurant_id', $restaurant->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Commandes par source
        $ordersBySource = Order::where('restaurant_id', $restaurant->id)
            ->select('order_source', DB::raw('COUNT(*) as count'))
            ->groupBy('order_source')
            ->get();

        return [
            'daily_revenue' => $dailyRevenue,
            'orders_by_source' => $ordersBySource,
        ];
    }

    /**
     * Obtenir l'activité récente
     */
    private function getRecentActivity($restaurant)
    {
        $activities = collect();

        // Commandes récentes
        $recentOrders = Order::where('restaurant_id', $restaurant->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($order) {
                return [
                    'type' => 'order',
                    'message' => "Nouvelle commande #{$order->order_number}",
                    'created_at' => $order->created_at,
                    'icon' => 'shopping-cart',
                    'color' => 'success'
                ];
            });

        // Nouveaux membres fidélité
        $newMembers = LoyaltyMember::where('restaurant_id', $restaurant->id)
            ->latest()
            ->take(3)
            ->get()
            ->map(function($member) {
                return [
                    'type' => 'loyalty',
                    'message' => "Nouveau membre fidélité: {$member->name}",
                    'created_at' => $member->created_at,
                    'icon' => 'star',
                    'color' => 'warning'
                ];
            });

        return $activities->merge($recentOrders)
            ->merge($newMembers)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }
}
