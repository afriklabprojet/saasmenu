<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardWidgetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur des widgets de dashboard
 */
class DashboardWidgetController extends Controller
{
    protected DashboardWidgetService $widgetService;

    public function __construct(DashboardWidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }

    /**
     * Obtenir tous les widgets du dashboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWidgets(Request $request): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $period = $request->input('period', 'today');

            $widgets = $this->widgetService->generateWidgets($vendorId, $period);

            return response()->json([
                'success' => true,
                'data' => $widgets,
                'period' => $period,
                'generated_at' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des widgets',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Obtenir widgets spécifiques par type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWidgetsByType(Request $request): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $period = $request->input('period', 'today');
            $types = $request->input('types', []); // ['summary_cards', 'charts', etc.]

            $allWidgets = $this->widgetService->generateWidgets($vendorId, $period);

            // Filtrer par types demandés
            $filteredWidgets = [];
            if (!empty($types)) {
                foreach ($types as $type) {
                    if (isset($allWidgets[$type])) {
                        $filteredWidgets[$type] = $allWidgets[$type];
                    }
                }
            } else {
                $filteredWidgets = $allWidgets;
            }

            return response()->json([
                'success' => true,
                'data' => $filteredWidgets,
                'requested_types' => $types,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du filtrage des widgets',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Obtenir configuration du dashboard
     *
     * @return JsonResponse
     */
    public function getDashboardConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'available_periods' => [
                    'today' => 'Aujourd\'hui',
                    'yesterday' => 'Hier',
                    'this_week' => 'Cette Semaine',
                    'last_week' => 'Semaine Dernière',
                    'this_month' => 'Ce Mois',
                    'last_month' => 'Mois Dernier',
                    'this_year' => 'Cette Année',
                    'custom' => 'Période Personnalisée'
                ],
                'widget_types' => [
                    'summary_cards' => 'Cartes de Résumé',
                    'charts' => 'Graphiques',
                    'insights_panel' => 'Panel d\'Insights',
                    'performance_indicators' => 'Indicateurs KPI',
                    'quick_actions' => 'Actions Rapides',
                    'alerts' => 'Alertes'
                ],
                'refresh_intervals' => [
                    300 => '5 minutes',
                    600 => '10 minutes',
                    1800 => '30 minutes',
                    3600 => '1 heure'
                ],
                'export_formats' => [
                    'pdf' => 'PDF',
                    'excel' => 'Excel',
                    'csv' => 'CSV',
                    'json' => 'JSON'
                ]
            ]
        ]);
    }

    /**
     * Actualiser widgets spécifiques
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshWidgets(Request $request): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $period = $request->input('period', 'today');
            $widgetIds = $request->input('widget_ids', []);

            // Forcer le rafraîchissement en supprimant le cache
            $cacheKey = "widgets_{$vendorId}_{$period}";
            cache()->forget($cacheKey);

            $widgets = $this->widgetService->generateWidgets($vendorId, $period);

            // Si des widgets spécifiques sont demandés
            if (!empty($widgetIds)) {
                $refreshedWidgets = [];
                foreach ($widgets as $type => $typeWidgets) {
                    if (is_array($typeWidgets)) {
                        foreach ($typeWidgets as $widget) {
                            if (isset($widget['id']) && in_array($widget['id'], $widgetIds)) {
                                $refreshedWidgets[$type][] = $widget;
                            }
                        }
                    }
                }
                $widgets = $refreshedWidgets;
            }

            return response()->json([
                'success' => true,
                'data' => $widgets,
                'refreshed_at' => now()->toDateTimeString(),
                'widget_ids' => $widgetIds
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'actualisation des widgets',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Obtenir métriques en temps réel
     *
     * @return JsonResponse
     */
    public function getRealTimeMetrics(): JsonResponse
    {
        try {
            $vendorId = auth()->id();

            // Métriques temps réel simples (à développer selon besoins)
            $metrics = [
                'active_orders' => \App\Models\Order::where('vendor_id', $vendorId)
                    ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
                    ->count(),
                'today_revenue' => \App\Models\Order::where('vendor_id', $vendorId)
                    ->whereDate('created_at', today())
                    ->where('order_status', 'delivered')
                    ->sum('order_total'),
                'online_customers' => 0, // À implémenter avec sessions actives
                'pending_notifications' => 0, // À implémenter
                'server_status' => 'online',
                'last_order_time' => \App\Models\Order::where('vendor_id', $vendorId)
                    ->latest()
                    ->first()?->created_at?->format('H:i:s') ?? 'Aucune',
                'timestamp' => now()->toDateTimeString()
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des métriques temps réel',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Exporter dashboard
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDashboard(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $period = $request->input('period', 'today');
            $format = $request->input('format', 'pdf');

            $widgets = $this->widgetService->generateWidgets($vendorId, $period);

            switch ($format) {
                case 'json':
                    return response()->json($widgets);

                case 'csv':
                    // Implémenter export CSV
                    return response()->json([
                        'success' => false,
                        'message' => 'Export CSV en cours de développement'
                    ]);

                case 'pdf':
                    // Implémenter export PDF
                    return response()->json([
                        'success' => false,
                        'message' => 'Export PDF en cours de développement'
                    ]);

                case 'excel':
                    // Implémenter export Excel
                    return response()->json([
                        'success' => false,
                        'message' => 'Export Excel en cours de développement'
                    ]);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Format d\'export non supporté'
                    ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export du dashboard',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Obtenir historique des performances
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPerformanceHistory(Request $request): JsonResponse
    {
        try {
            $vendorId = auth()->id();
            $days = $request->input('days', 7);

            $history = [];
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($i);
                $cacheKey = "widgets_{$vendorId}_" . $date->format('Y-m-d');

                $dayData = cache()->get($cacheKey);
                if ($dayData) {
                    $history[] = [
                        'date' => $date->format('Y-m-d'),
                        'revenue' => $dayData['summary_cards'][0]['value'] ?? 0,
                        'orders' => $dayData['summary_cards'][1]['value'] ?? 0,
                        'customers' => $dayData['summary_cards'][2]['value'] ?? 0
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => array_reverse($history),
                'period_days' => $days
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => app()->environment('local') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
