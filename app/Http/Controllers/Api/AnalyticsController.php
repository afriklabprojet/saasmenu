<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\BusinessIntelligenceService;
use App\Services\DeferredExecutionService;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected BusinessIntelligenceService $biService;
    protected DeferredExecutionService $deferredService;

    public function __construct(
        BusinessIntelligenceService $biService,
        DeferredExecutionService $deferredService
    ) {
        $this->biService = $biService;
        $this->deferredService = $deferredService;
    }

    /**
     * Dashboard principal Analytics
     * GET /api/analytics/dashboard/{vendorId}?period=today
     */
    public function dashboard(Request $request, int $vendorId): JsonResponse
    {
        $startTime = microtime(true);

        $period = $request->get('period', 'today');
        $validPeriods = ['today', 'week', 'month', 'year'];

        if (!in_array($period, $validPeriods)) {
            return response()->json([
                'error' => 'Période invalide',
                'valid_periods' => $validPeriods
            ], 400);
        }

        try {
            // Récupérer le dashboard principal
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            // Programmer analytics tracking en arrière-plan
            $this->deferredService->deferAnalytics([
                'action' => 'dashboard_view',
                'vendor_id' => $vendorId,
                'period' => $period,
                'timestamp' => now()
            ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'success' => true,
                'data' => $dashboard,
                'meta' => [
                    'vendor_id' => $vendorId,
                    'period' => $period,
                    'response_time_ms' => $responseTime,
                    'cache_enabled' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics dashboard error', [
                'vendor_id' => $vendorId,
                'period' => $period,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Erreur lors de la génération du dashboard',
                'message' => 'Veuillez réessayer plus tard'
            ], 500);
        }
    }

    /**
     * Métriques de revenue détaillées
     * GET /api/analytics/revenue/{vendorId}
     */
    public function revenue(Request $request, int $vendorId): JsonResponse
    {
        $period = $request->get('period', 'month');

        try {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue_metrics' => $dashboard['revenue'],
                    'trends' => $dashboard['trends']['daily_trend'] ?? [],
                    'forecasts' => $dashboard['trends']['performance_forecast'] ?? []
                ],
                'period' => $period
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des métriques revenue'
            ], 500);
        }
    }

    /**
     * Métriques produits et performances
     * GET /api/analytics/products/{vendorId}
     */
    public function products(Request $request, int $vendorId): JsonResponse
    {
        $period = $request->get('period', 'month');

        try {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            return response()->json([
                'success' => true,
                'data' => $dashboard['products'],
                'insights' => array_filter($dashboard['insights'],
                    fn($insight) => $insight['type'] === 'highlight'
                )
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des métriques produits'
            ], 500);
        }
    }

    /**
     * Métriques clients
     * GET /api/analytics/customers/{vendorId}
     */
    public function customers(Request $request, int $vendorId): JsonResponse
    {
        $period = $request->get('period', 'month');

        try {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            return response()->json([
                'success' => true,
                'data' => $dashboard['customers'],
                'retention_insights' => $this->generateRetentionInsights($dashboard['customers'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des métriques clients'
            ], 500);
        }
    }

    /**
     * Insights business automatiques
     * GET /api/analytics/insights/{vendorId}
     */
    public function insights(Request $request, int $vendorId): JsonResponse
    {
        $period = $request->get('period', 'week');

        try {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            return response()->json([
                'success' => true,
                'insights' => $dashboard['insights'],
                'recommendations' => $this->generateRecommendations($dashboard),
                'alerts' => $this->generateAlerts($dashboard)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la génération des insights'
            ], 500);
        }
    }

    /**
     * Données temps réel (5 minutes)
     * GET /api/analytics/realtime/{vendorId}
     */
    public function realtime(int $vendorId): JsonResponse
    {
        try {
            $realtimeData = $this->biService->getMainDashboard($vendorId, 'today');

            // Extraire données des dernières 24h pour simulation temps réel
            return response()->json([
                'success' => true,
                'realtime' => [
                    'current_orders' => $realtimeData['orders']['total_orders'],
                    'current_revenue' => $realtimeData['revenue']['total_revenue'],
                    'active_customers' => $realtimeData['customers']['new_customers'],
                    'last_updated' => now()->format('H:i:s'),
                    'status' => 'live'
                ],
                'hourly_breakdown' => $realtimeData['revenue']['hourly_breakdown']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur données temps réel'
            ], 500);
        }
    }

    /**
     * Export des données analytics
     * GET /api/analytics/export/{vendorId}?format=json|csv
     */
    public function export(Request $request, int $vendorId): JsonResponse
    {
        $format = $request->get('format', 'json');
        $period = $request->get('period', 'month');

        try {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            // Programmer génération export en arrière-plan
            $this->deferredService->defer('analytics_export', [
                'vendor_id' => $vendorId,
                'period' => $period,
                'format' => $format,
                'requested_at' => now()
            ], 0, 'analytics');

            return response()->json([
                'success' => true,
                'message' => 'Export en cours de génération',
                'export_id' => 'exp_' . $vendorId . '_' . time(),
                'estimated_completion' => '2-3 minutes',
                'download_ready' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du lancement de l\'export'
            ], 500);
        }
    }

    /**
     * Comparaison de périodes
     * GET /api/analytics/compare/{vendorId}?current=month&previous=month
     */
    public function compare(Request $request, int $vendorId): JsonResponse
    {
        $currentPeriod = $request->get('current', 'month');
        $previousPeriod = $request->get('previous', 'month');

        try {
            $currentData = $this->biService->getMainDashboard($vendorId, $currentPeriod);
            // Note: Pour une vraie comparaison, il faudrait modifier le service
            // pour accepter des dates personnalisées

            return response()->json([
                'success' => true,
                'comparison' => [
                    'current_period' => $currentData,
                    'growth_analysis' => [
                        'revenue_growth' => $currentData['revenue']['growth_percentage'],
                        'order_growth' => 'À calculer avec période précédente',
                        'customer_growth' => 'À calculer avec période précédente'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la comparaison'
            ], 500);
        }
    }

    /**
     * Générer insights de rétention
     */
    protected function generateRetentionInsights(array $customerData): array
    {
        $insights = [];

        if ($customerData['retention_rate'] > 70) {
            $insights[] = [
                'type' => 'excellent',
                'message' => 'Excellent taux de rétention client!'
            ];
        } elseif ($customerData['retention_rate'] < 30) {
            $insights[] = [
                'type' => 'warning',
                'message' => 'Taux de rétention faible - programmes fidélité recommandés'
            ];
        }

        return $insights;
    }

    /**
     * Générer recommandations business
     */
    protected function generateRecommendations(array $dashboard): array
    {
        $recommendations = [];

        // Recommandation basée sur croissance revenue
        if ($dashboard['revenue']['growth_percentage'] < 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Analyser les produits moins performants',
                'description' => 'Le CA est en baisse, concentrez-vous sur vos best-sellers'
            ];
        }

        // Recommandation basée sur taux de complétion
        if ($dashboard['orders']['completion_rate'] < 85) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Améliorer le processus de commande',
                'description' => 'Taux d\'annulation élevé - vérifiez les délais et la qualité'
            ];
        }

        return $recommendations;
    }

    /**
     * Générer alertes automatiques
     */
    protected function generateAlerts(array $dashboard): array
    {
        $alerts = [];

        // Alerte revenue en chute
        if ($dashboard['revenue']['growth_percentage'] < -20) {
            $alerts[] = [
                'severity' => 'critical',
                'message' => 'Chute significative du chiffre d\'affaires (-20%+)',
                'action_required' => true
            ];
        }

        // Alerte taux annulation élevé
        if ($dashboard['orders']['completion_rate'] < 70) {
            $alerts[] = [
                'severity' => 'warning',
                'message' => 'Taux d\'annulation des commandes élevé',
                'action_required' => true
            ];
        }

        return $alerts;
    }
}
