<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Service Business Intelligence pour RestroSaaS
 * Génère KPIs automatiques et insights business pour restaurateurs
 */
class BusinessIntelligenceService
{
    protected $cachePrefix = 'bi_';
    protected $cacheTTL = 3600; // 1 heure

    /**
     * Dashboard principal avec KPIs essentiels
     */
    public function getMainDashboard(int $vendorId, string $period = 'today'): array
    {
        $cacheKey = $this->cachePrefix . "dashboard_{$vendorId}_{$period}";

        return Cache::remember($cacheKey, $this->cacheTTL, function() use ($vendorId, $period) {
            $dateRange = $this->getDateRange($period);

            return [
                'revenue' => $this->getRevenueMetrics($vendorId, $dateRange),
                'orders' => $this->getOrderMetrics($vendorId, $dateRange),
                'customers' => $this->getCustomerMetrics($vendorId, $dateRange),
                'products' => $this->getProductMetrics($vendorId, $dateRange),
                'trends' => $this->getTrendAnalysis($vendorId, $dateRange),
                'insights' => $this->generateBusinessInsights($vendorId, $dateRange),
                'generated_at' => now(),
                'period' => $period
            ];
        });
    }

    /**
     * Métriques de chiffre d'affaires
     */
    protected function getRevenueMetrics(int $vendorId, array $dateRange): array
    {
        $currentPeriod = $this->getRevenuePeriod($vendorId, $dateRange['start'], $dateRange['end']);
        $previousPeriod = $this->getRevenuePeriod($vendorId, $dateRange['previous_start'], $dateRange['previous_end']);

        $revenueGrowth = $previousPeriod > 0 ?
            round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2) : 0;

        return [
            'total_revenue' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'growth_percentage' => $revenueGrowth,
            'growth_trend' => $revenueGrowth > 0 ? 'up' : ($revenueGrowth < 0 ? 'down' : 'stable'),
            'daily_average' => $this->getDailyAverageRevenue($vendorId, $dateRange),
            'hourly_breakdown' => $this->getHourlyRevenueBreakdown($vendorId, $dateRange)
        ];
    }

    /**
     * Métriques des commandes
     */
    protected function getOrderMetrics(int $vendorId, array $dateRange): array
    {
        $ordersQuery = DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        $totalOrders = $ordersQuery->count();
        $completedOrders = (clone $ordersQuery)->where('status', 5)->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 3)->count();

        $avgOrderValue = $ordersQuery->avg('grand_total') ?? 0;
        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'completion_rate' => $completionRate,
            'average_order_value' => round($avgOrderValue, 2),
            'peak_hours' => $this->getPeakOrderHours($vendorId, $dateRange),
            'order_status_breakdown' => $this->getOrderStatusBreakdown($vendorId, $dateRange)
        ];
    }

    /**
     * Métriques clients
     */
    protected function getCustomerMetrics(int $vendorId, array $dateRange): array
    {
        $newCustomers = DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('user_id')
            ->count();

        $returningCustomers = $this->getReturningCustomers($vendorId, $dateRange);
        $customerRetentionRate = $newCustomers > 0 ?
            round(($returningCustomers / $newCustomers) * 100, 2) : 0;

        return [
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'retention_rate' => $customerRetentionRate,
            'top_customers' => $this->getTopCustomers($vendorId, $dateRange),
            'customer_lifetime_value' => $this->calculateCustomerLifetimeValue($vendorId, $dateRange)
        ];
    }

    /**
     * Métriques des produits
     */
    protected function getProductMetrics(int $vendorId, array $dateRange): array
    {
        $topProducts = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->select('order_details.product_name',
                     DB::raw('SUM(order_details.qty) as total_sold'),
                     DB::raw('SUM(order_details.product_price * order_details.qty) as total_revenue'))
            ->groupBy('products.id', 'order_details.product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return [
            'top_selling_products' => $topProducts,
            'product_performance' => $this->analyzeProductPerformance($vendorId, $dateRange),
            'category_breakdown' => $this->getCategoryBreakdown($vendorId, $dateRange)
        ];
    }

    /**
     * Analyse des tendances
     */
    protected function getTrendAnalysis(int $vendorId, array $dateRange): array
    {
        return [
            'daily_trend' => $this->getDailyTrend($vendorId, $dateRange),
            'weekly_pattern' => $this->getWeeklyPattern($vendorId, $dateRange),
            'seasonal_insights' => $this->getSeasonalInsights($vendorId),
            'performance_forecast' => $this->generateForecast($vendorId, $dateRange)
        ];
    }

    /**
     * Génération d'insights business automatiques
     */
    protected function generateBusinessInsights(int $vendorId, array $dateRange): array
    {
        $insights = [];

        // Analyse croissance
        $revenueMetrics = $this->getRevenueMetrics($vendorId, $dateRange);
        if ($revenueMetrics['growth_percentage'] > 10) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Croissance Excellente',
                'message' => "Votre chiffre d'affaires a augmenté de {$revenueMetrics['growth_percentage']}% cette période!",
                'action' => 'Continuez sur cette lancée en analysant vos produits les plus performants.'
            ];
        }

        // Analyse des heures de pointe
        $peakHours = $this->getPeakOrderHours($vendorId, $dateRange);
        if (!empty($peakHours)) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Optimisation Horaires',
                'message' => "Vos heures de pointe: " . implode(', ', array_keys($peakHours)),
                'action' => 'Ajustez vos stocks et personnel pour ces créneaux.'
            ];
        }

        // Analyse des produits
        $productMetrics = $this->getProductMetrics($vendorId, $dateRange);
        $topProduct = $productMetrics['top_selling_products']->first();
        if ($topProduct) {
            $insights[] = [
                'type' => 'highlight',
                'title' => 'Produit Star',
                'message' => "'{$topProduct->product_name}' est votre produit le plus vendu ({$topProduct->total_sold} unités).",
                'action' => 'Créez des offres similaires ou des variantes de ce produit.'
            ];
        }

        return $insights;
    }

    /**
     * Obtenir la plage de dates selon la période
     */
    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subDay()->startOfDay(),
                'previous_end' => $now->copy()->subDay()->endOfDay()
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
                'previous_start' => $now->copy()->subWeek()->startOfWeek(),
                'previous_end' => $now->copy()->subWeek()->endOfWeek()
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'previous_start' => $now->copy()->subMonth()->startOfMonth(),
                'previous_end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'previous_start' => $now->copy()->subYear()->startOfYear(),
                'previous_end' => $now->copy()->subYear()->endOfYear()
            ],
            default => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subDay()->startOfDay(),
                'previous_end' => $now->copy()->subDay()->endOfDay()
            ]
        };
    }

    /**
     * Calculer le chiffre d'affaires pour une période
     */
    protected function getRevenuePeriod(int $vendorId, Carbon $start, Carbon $end): float
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->where('status', 5)
            ->whereBetween('created_at', [$start, $end])
            ->sum('grand_total') ?? 0;
    }

    /**
     * Moyennes journalières de CA
     */
    protected function getDailyAverageRevenue(int $vendorId, array $dateRange): float
    {
        $days = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $totalRevenue = $this->getRevenuePeriod($vendorId, $dateRange['start'], $dateRange['end']);

        return $days > 0 ? round($totalRevenue / $days, 2) : 0;
    }

    /**
     * Répartition du CA par heure
     */
    protected function getHourlyRevenueBreakdown(int $vendorId, array $dateRange): array
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->where('status', 5)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw('HOUR(created_at) as hour'),
                     DB::raw('SUM(grand_total) as revenue'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->pluck('revenue', 'hour')
            ->toArray();
    }

    /**
     * Heures de pointe pour les commandes
     */
    protected function getPeakOrderHours(int $vendorId, array $dateRange): array
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw('HOUR(created_at) as hour'),
                     DB::raw('COUNT(*) as order_count'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('order_count', 'desc')
            ->limit(3)
            ->pluck('order_count', 'hour')
            ->toArray();
    }

    /**
     * Répartition des statuts de commandes
     */
    protected function getOrderStatusBreakdown(int $vendorId, array $dateRange): array
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Nombre de clients récurrents
     */
    protected function getReturningCustomers(int $vendorId, array $dateRange): int
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('user_id', function($query) use ($vendorId, $dateRange) {
                $query->select('user_id')
                      ->from('orders')
                      ->where('vendor_id', $vendorId)
                      ->where('created_at', '<', $dateRange['start'])
                      ->distinct();
            })
            ->distinct('user_id')
            ->count();
    }

    /**
     * Top clients
     */
    protected function getTopCustomers(int $vendorId, array $dateRange): array
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->where('status', 5)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('user_id',
                     DB::raw('COUNT(*) as order_count'),
                     DB::raw('SUM(grand_total) as total_spent'))
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Valeur vie client
     */
    protected function calculateCustomerLifetimeValue(int $vendorId, array $dateRange): float
    {
        $avgOrderValue = DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->where('status', 5)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->avg('grand_total') ?? 0;

        $avgOrderFrequency = 2.5; // Estimation basée sur données historiques
        $avgCustomerLifespan = 12; // Mois

        return round($avgOrderValue * $avgOrderFrequency * $avgCustomerLifespan, 2);
    }

    /**
     * Analyse performance produits
     */
    protected function analyzeProductPerformance(int $vendorId, array $dateRange): array
    {
        return DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->select('order_details.product_name',
                     DB::raw('COUNT(order_details.id) as orders_count'),
                     DB::raw('SUM(order_details.qty) as total_quantity'),
                     DB::raw('AVG(order_details.product_price) as avg_price'),
                     DB::raw('SUM(order_details.product_price * order_details.qty) as total_revenue'))
            ->groupBy('products.id', 'order_details.product_name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Répartition par catégorie
     */
    protected function getCategoryBreakdown(int $vendorId, array $dateRange): array
    {
        return DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->select('categories.name',
                     DB::raw('SUM(order_details.qty) as total_sold'),
                     DB::raw('SUM(order_details.product_price * order_details.qty) as total_revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Tendance quotidienne
     */
    protected function getDailyTrend(int $vendorId, array $dateRange): array
    {
        $period = CarbonPeriod::create($dateRange['start'], $dateRange['end']);
        $dailyData = [];

        foreach ($period as $date) {
            $dayRevenue = $this->getRevenuePeriod($vendorId, $date->copy()->startOfDay(), $date->copy()->endOfDay());
            $dayOrders = DB::table('orders')
                ->where('vendor_id', $vendorId)
                ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->count();

            $dailyData[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => $dayRevenue,
                'orders' => $dayOrders
            ];
        }

        return $dailyData;
    }

    /**
     * Pattern hebdomadaire
     */
    protected function getWeeklyPattern(int $vendorId, array $dateRange): array
    {
        return DB::table('orders')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw('DAYNAME(created_at) as day_name'),
                     DB::raw('COUNT(*) as order_count'),
                     DB::raw('SUM(grand_total) as revenue'))
            ->groupBy(DB::raw('DAYNAME(created_at)'))
            ->orderBy(DB::raw('DAYOFWEEK(created_at)'))
            ->get()
            ->toArray();
    }

    /**
     * Insights saisonniers
     */
    protected function getSeasonalInsights(int $vendorId): array
    {
        // Analyse basée sur les 12 derniers mois
        return [
            'best_month' => 'À implémenter avec plus de données historiques',
            'seasonal_trends' => 'Analyse saisonnière nécessite 1 an de données'
        ];
    }

    /**
     * Prévisions simples
     */
    protected function generateForecast(int $vendorId, array $dateRange): array
    {
        $dailyTrend = $this->getDailyTrend($vendorId, $dateRange);

        if (count($dailyTrend) < 7) {
            return ['message' => 'Données insuffisantes pour prévision'];
        }

        $recentRevenues = array_slice(array_column($dailyTrend, 'revenue'), -7);
        $avgDailyRevenue = array_sum($recentRevenues) / count($recentRevenues);

        return [
            'next_day_forecast' => round($avgDailyRevenue, 2),
            'next_week_forecast' => round($avgDailyRevenue * 7, 2),
            'confidence' => 'medium',
            'trend_direction' => $this->analyzeTrendDirection($recentRevenues)
        ];
    }

    /**
     * Analyser direction de tendance
     */
    protected function analyzeTrendDirection(array $values): string
    {
        if (count($values) < 2) return 'stable';

        $firstHalf = array_slice($values, 0, ceil(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        if ($secondAvg > $firstAvg * 1.1) return 'up';
        if ($secondAvg < $firstAvg * 0.9) return 'down';
        return 'stable';
    }
}
