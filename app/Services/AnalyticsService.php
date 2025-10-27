<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service d'analytics et de rapports pour le restaurant
 *
 * Fournit des statistiques détaillées sur :
 * - Chiffre d'affaires en temps réel
 * - Plats les plus vendus
 * - Heures de pointe
 * - Analyse client et fidélisation
 */
class AnalyticsService
{
    /**
     * Obtenir les statistiques du chiffre d'affaires en temps réel
     *
     * @param int $vendor_id ID du restaurant
     * @param string $period Période (today, week, month, year)
     * @return array Statistiques du CA
     */
    public function getRevenueStats(int $vendor_id, string $period = 'today'): array
    {
        $query = Order::where('vendor_id', $vendor_id)
            ->where('status', 2) // Commandes complétées
            ->where('payment_status', 'paid'); // Payées

        // Définir la période
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                $previousQuery = Order::where('vendor_id', $vendor_id)
                    ->where('status', 2)
                    ->where('payment_status', 'paid')
                    ->whereDate('created_at', Carbon::yesterday());
                break;

            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                $previousQuery = Order::where('vendor_id', $vendor_id)
                    ->where('status', 2)
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [
                        Carbon::now()->subWeek()->startOfWeek(),
                        Carbon::now()->subWeek()->endOfWeek()
                    ]);
                break;

            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                $previousQuery = Order::where('vendor_id', $vendor_id)
                    ->where('status', 2)
                    ->where('payment_status', 'paid')
                    ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;

            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                $previousQuery = Order::where('vendor_id', $vendor_id)
                    ->where('status', 2)
                    ->where('payment_status', 'paid')
                    ->whereYear('created_at', Carbon::now()->subYear()->year);
                break;

            default:
                $query->whereDate('created_at', Carbon::today());
                $previousQuery = null;
        }

        // Statistiques actuelles
        $currentRevenue = $query->sum('grand_total');
        $currentOrders = $query->count();
        $currentAvgOrder = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

        // Statistiques précédentes pour comparaison
        $previousRevenue = $previousQuery ? $previousQuery->sum('grand_total') : 0;
        $previousOrders = $previousQuery ? $previousQuery->count() : 0;

        // Calculer les variations
        $revenueChange = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        $ordersChange = $previousOrders > 0
            ? (($currentOrders - $previousOrders) / $previousOrders) * 100
            : 0;

        return [
            'current' => [
                'revenue' => $currentRevenue,
                'orders' => $currentOrders,
                'avg_order' => round($currentAvgOrder, 2),
            ],
            'previous' => [
                'revenue' => $previousRevenue,
                'orders' => $previousOrders,
            ],
            'change' => [
                'revenue' => round($revenueChange, 2),
                'orders' => round($ordersChange, 2),
            ],
            'period' => $period,
        ];
    }

    /**
     * Obtenir les plats les plus vendus
     *
     * @param int $vendor_id ID du restaurant
     * @param int $limit Nombre de résultats
     * @param string $period Période d'analyse
     * @return array Top des plats
     */
    public function getTopSellingItems(int $vendor_id, int $limit = 10, string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $topItems = OrderDetails::select(
                'order_details.product_id',
                'order_details.product_name',
                DB::raw('SUM(order_details.qty) as total_quantity'),
                DB::raw('SUM(order_details.qty * order_details.product_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_details.order_id) as order_count')
            )
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.vendor_id', $vendor_id)
            ->where('orders.status', 2) // Complétées
            ->where('orders.created_at', '>=', $startDate)
            ->groupBy('order_details.product_id', 'order_details.product_name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($topItems as $item) {
            // Récupérer les détails du produit
            $productDetails = Item::find($item->product_id);

            $items[] = [
                'item_id' => $item->product_id,
                'item_name' => $item->product_name,
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
                'order_count' => $item->order_count,
                'avg_price' => $item->total_quantity > 0
                    ? round($item->total_revenue / $item->total_quantity, 2)
                    : 0,
                'image' => $productDetails->image ?? null,
                'category' => $productDetails->category->name ?? 'N/A',
            ];
        }

        return $items;
    }

    /**
     * Analyser les heures de pointe
     *
     * @param int $vendor_id ID du restaurant
     * @param string $period Période d'analyse
     * @return array Statistiques par heure
     */
    public function getPeakHours(int $vendor_id, string $period = 'week'): array
    {
        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfWeek(),
        };

        $ordersByHour = Order::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(grand_total) as revenue'),
                DB::raw('AVG(grand_total) as avg_order_value')
            )
            ->where('vendor_id', $vendor_id)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $hourlyStats = [];
        for ($h = 0; $h < 24; $h++) {
            $hourData = $ordersByHour->firstWhere('hour', $h);

            $hourlyStats[] = [
                'hour' => $h,
                'hour_label' => sprintf('%02d:00', $h),
                'order_count' => $hourData ? $hourData->order_count : 0,
                'revenue' => $hourData ? $hourData->revenue : 0,
                'avg_order_value' => $hourData ? round($hourData->avg_order_value, 2) : 0,
            ];
        }

        // Identifier les 3 heures les plus chargées
        $topHours = collect($hourlyStats)
            ->sortByDesc('order_count')
            ->take(3)
            ->values()
            ->all();

        return [
            'hourly_stats' => $hourlyStats,
            'peak_hours' => $topHours,
            'total_orders' => $ordersByHour->sum('order_count'),
            'period' => $period,
        ];
    }

    /**
     * Analyse des clients et fidélisation
     *
     * @param int $vendor_id ID du restaurant
     * @param string $period Période d'analyse
     * @return array Statistiques clients
     */
    public function getCustomerAnalytics(int $vendor_id, string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        // Total de clients uniques
        $totalCustomers = Order::where('vendor_id', $vendor_id)
            ->where('created_at', '>=', $startDate)
            ->distinct('user_mobile')
            ->count('user_mobile');

        // Nouveaux clients (première commande dans la période)
        $newCustomers = Order::where('vendor_id', $vendor_id)
            ->where('created_at', '>=', $startDate)
            ->whereNotIn('user_mobile', function($query) use ($vendor_id, $startDate) {
                $query->select('user_mobile')
                    ->from('orders')
                    ->where('vendor_id', $vendor_id)
                    ->where('created_at', '<', $startDate);
            })
            ->distinct('user_mobile')
            ->count('user_mobile');

        // Clients récurrents
        $recurringCustomers = Order::select('user_mobile', DB::raw('COUNT(*) as order_count'))
            ->where('vendor_id', $vendor_id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_mobile')
            ->having('order_count', '>', 1)
            ->count();

        // Top 10 clients
        $topCustomers = Order::select(
                'user_mobile',
                'user_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(grand_total) as total_spent'),
                DB::raw('AVG(grand_total) as avg_order'),
                DB::raw('MAX(created_at) as last_order')
            )
            ->where('vendor_id', $vendor_id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_mobile', 'user_name')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function($customer) {
                return [
                    'mobile' => $customer->user_mobile,
                    'name' => $customer->user_name,
                    'order_count' => $customer->order_count,
                    'total_spent' => $customer->total_spent,
                    'avg_order' => round($customer->avg_order, 2),
                    'last_order' => Carbon::parse($customer->last_order)->diffForHumans(),
                ];
            })
            ->toArray();

        // Taux de rétention
        $retentionRate = $totalCustomers > 0
            ? round(($recurringCustomers / $totalCustomers) * 100, 2)
            : 0;

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'recurring_customers' => $recurringCustomers,
            'retention_rate' => $retentionRate,
            'top_customers' => $topCustomers,
            'period' => $period,
        ];
    }

    /**
     * Obtenir un tableau de bord complet
     *
     * @param int $vendor_id ID du restaurant
     * @param string $period Période par défaut
     * @return array Dashboard complet
     */
    public function getCompleteDashboard(int $vendor_id, string $period = 'today'): array
    {
        return [
            'revenue' => $this->getRevenueStats($vendor_id, $period),
            'top_items' => $this->getTopSellingItems($vendor_id, 10, $period),
            'peak_hours' => $this->getPeakHours($vendor_id, $period === 'today' ? 'week' : $period),
            'customer_analytics' => $this->getCustomerAnalytics($vendor_id, $period),
            'generated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Obtenir les statistiques de performance des catégories
     *
     * @param int $vendor_id ID du restaurant
     * @param string $period Période d'analyse
     * @return array Performance par catégorie
     */
    public function getCategoryPerformance(int $vendor_id, string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $categoryStats = OrderDetails::select(
                'categories.name as category_name',
                DB::raw('COUNT(order_details.id) as items_sold'),
                DB::raw('SUM(order_details.qty) as total_quantity'),
                DB::raw('SUM(order_details.qty * order_details.product_price) as revenue')
            )
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('items', 'order_details.product_id', '=', 'items.id')
            ->join('categories', 'items.cat_id', '=', 'categories.id')
            ->where('orders.vendor_id', $vendor_id)
            ->where('orders.status', 2)
            ->where('orders.created_at', '>=', $startDate)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function($cat) {
                return [
                    'category_name' => $cat->category_name,
                    'items_sold' => $cat->items_sold,
                    'total_quantity' => $cat->total_quantity,
                    'revenue' => $cat->revenue,
                ];
            })
            ->toArray();

        return $categoryStats;
    }

    /**
     * Comparer les performances entre deux périodes
     *
     * @param int $vendor_id ID du restaurant
     * @param string $currentPeriodStart Date début période actuelle
     * @param string $currentPeriodEnd Date fin période actuelle
     * @param string $previousPeriodStart Date début période précédente
     * @param string $previousPeriodEnd Date fin période précédente
     * @return array Comparaison
     */
    public function comparePeriods(
        int $vendor_id,
        string $currentPeriodStart,
        string $currentPeriodEnd,
        string $previousPeriodStart,
        string $previousPeriodEnd
    ): array {
        $currentStats = Order::where('vendor_id', $vendor_id)
            ->where('status', 2)
            ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(grand_total) as revenue,
                AVG(grand_total) as avg_order,
                COUNT(DISTINCT user_mobile) as unique_customers
            ')
            ->first();

        $previousStats = Order::where('vendor_id', $vendor_id)
            ->where('status', 2)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(grand_total) as revenue,
                AVG(grand_total) as avg_order,
                COUNT(DISTINCT user_mobile) as unique_customers
            ')
            ->first();

        $calculateChange = function($current, $previous) {
            return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0;
        };

        return [
            'current_period' => [
                'start' => $currentPeriodStart,
                'end' => $currentPeriodEnd,
                'orders' => $currentStats->total_orders ?? 0,
                'revenue' => $currentStats->revenue ?? 0,
                'avg_order' => round($currentStats->avg_order ?? 0, 2),
                'customers' => $currentStats->unique_customers ?? 0,
            ],
            'previous_period' => [
                'start' => $previousPeriodStart,
                'end' => $previousPeriodEnd,
                'orders' => $previousStats->total_orders ?? 0,
                'revenue' => $previousStats->revenue ?? 0,
                'avg_order' => round($previousStats->avg_order ?? 0, 2),
                'customers' => $previousStats->unique_customers ?? 0,
            ],
            'changes' => [
                'orders' => $calculateChange(
                    $currentStats->total_orders ?? 0,
                    $previousStats->total_orders ?? 0
                ),
                'revenue' => $calculateChange(
                    $currentStats->revenue ?? 0,
                    $previousStats->revenue ?? 0
                ),
                'avg_order' => $calculateChange(
                    $currentStats->avg_order ?? 0,
                    $previousStats->avg_order ?? 0
                ),
                'customers' => $calculateChange(
                    $currentStats->unique_customers ?? 0,
                    $previousStats->unique_customers ?? 0
                ),
            ],
        ];
    }
}
