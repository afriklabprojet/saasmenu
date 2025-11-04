<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Service de génération de widgets dashboard intelligents
 */
class DashboardWidgetService
{
    protected BusinessIntelligenceService $biService;

    public function __construct(BusinessIntelligenceService $biService)
    {
        $this->biService = $biService;
    }

    /**
     * Générer collection de widgets pour dashboard
     */
    public function generateWidgets(int $vendorId, string $period = 'today'): array
    {
        $cacheKey = "widgets_{$vendorId}_{$period}";

        return Cache::remember($cacheKey, 1800, function() use ($vendorId, $period) {
            $dashboard = $this->biService->getMainDashboard($vendorId, $period);

            return [
                'summary_cards' => $this->generateSummaryCards($dashboard),
                'charts' => $this->generateCharts($dashboard),
                'insights_panel' => $this->generateInsightsPanel($dashboard),
                'performance_indicators' => $this->generateKPIs($dashboard),
                'quick_actions' => $this->generateQuickActions($dashboard),
                'alerts' => $this->generateAlertWidgets($dashboard)
            ];
        });
    }

    /**
     * Cartes de résumé (Revenue, Orders, etc.)
     */
    protected function generateSummaryCards(array $dashboard): array
    {
        return [
            [
                'id' => 'revenue_card',
                'title' => 'Chiffre d\'Affaires',
                'value' => number_format($dashboard['revenue']['total_revenue'], 2) . ' €',
                'change' => $dashboard['revenue']['growth_percentage'],
                'trend' => $dashboard['revenue']['growth_trend'],
                'icon' => 'euro-sign',
                'color' => $this->getTrendColor($dashboard['revenue']['growth_trend']),
                'subtitle' => 'vs période précédente'
            ],
            [
                'id' => 'orders_card',
                'title' => 'Commandes',
                'value' => number_format($dashboard['orders']['total_orders']),
                'change' => $this->calculateOrderGrowth($dashboard),
                'trend' => 'up', // À calculer
                'icon' => 'shopping-cart',
                'color' => 'blue',
                'subtitle' => $dashboard['orders']['completed_orders'] . ' complétées'
            ],
            [
                'id' => 'customers_card',
                'title' => 'Nouveaux Clients',
                'value' => number_format($dashboard['customers']['new_customers']),
                'change' => 0, // À calculer avec période précédente
                'trend' => 'stable',
                'icon' => 'users',
                'color' => 'green',
                'subtitle' => 'Rétention: ' . $dashboard['customers']['retention_rate'] . '%'
            ],
            [
                'id' => 'avg_order_card',
                'title' => 'Panier Moyen',
                'value' => number_format($dashboard['orders']['average_order_value'], 2) . ' €',
                'change' => 0, // À calculer
                'trend' => 'stable',
                'icon' => 'calculator',
                'color' => 'purple',
                'subtitle' => 'Par commande'
            ]
        ];
    }

    /**
     * Configuration des graphiques
     */
    protected function generateCharts(array $dashboard): array
    {
        return [
            [
                'id' => 'revenue_trend',
                'type' => 'line',
                'title' => 'Évolution du CA',
                'data' => $this->formatTrendData($dashboard['trends']['daily_trend'] ?? []),
                'options' => [
                    'responsive' => true,
                    'scales' => [
                        'y' => ['beginAtZero' => true]
                    ]
                ]
            ],
            [
                'id' => 'orders_hourly',
                'type' => 'bar',
                'title' => 'Commandes par Heure',
                'data' => $this->formatHourlyData($dashboard['revenue']['hourly_breakdown']),
                'options' => [
                    'responsive' => true,
                    'plugins' => [
                        'legend' => ['display' => false]
                    ]
                ]
            ],
            [
                'id' => 'products_performance',
                'type' => 'doughnut',
                'title' => 'Top Produits',
                'data' => $this->formatProductsData($dashboard['products']['top_selling_products']),
                'options' => [
                    'responsive' => true,
                    'plugins' => [
                        'legend' => ['position' => 'right']
                    ]
                ]
            ],
            [
                'id' => 'weekly_pattern',
                'type' => 'radar',
                'title' => 'Pattern Hebdomadaire',
                'data' => $this->formatWeeklyPattern($dashboard['trends']['weekly_pattern'] ?? []),
                'options' => [
                    'responsive' => true,
                    'scale' => [
                        'ticks' => ['beginAtZero' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Panel d'insights business
     */
    protected function generateInsightsPanel(array $dashboard): array
    {
        $insights = $dashboard['insights'];

        return [
            'title' => 'Insights Business',
            'insights' => array_map(function($insight) {
                return [
                    'type' => $insight['type'],
                    'title' => $insight['title'],
                    'message' => $insight['message'],
                    'action' => $insight['action'] ?? null,
                    'icon' => $this->getInsightIcon($insight['type']),
                    'priority' => $this->getInsightPriority($insight['type'])
                ];
            }, $insights),
            'total_insights' => count($insights)
        ];
    }

    /**
     * Indicateurs de performance clés
     */
    protected function generateKPIs(array $dashboard): array
    {
        return [
            [
                'name' => 'Taux de Complétion',
                'value' => $dashboard['orders']['completion_rate'],
                'unit' => '%',
                'target' => 95,
                'status' => $dashboard['orders']['completion_rate'] >= 90 ? 'excellent' :
                           ($dashboard['orders']['completion_rate'] >= 80 ? 'good' : 'needs_improvement'),
                'color' => $dashboard['orders']['completion_rate'] >= 90 ? 'green' :
                          ($dashboard['orders']['completion_rate'] >= 80 ? 'orange' : 'red')
            ],
            [
                'name' => 'Rétention Client',
                'value' => $dashboard['customers']['retention_rate'],
                'unit' => '%',
                'target' => 60,
                'status' => $dashboard['customers']['retention_rate'] >= 60 ? 'excellent' : 'good',
                'color' => $dashboard['customers']['retention_rate'] >= 60 ? 'green' : 'orange'
            ],
            [
                'name' => 'Croissance CA',
                'value' => abs($dashboard['revenue']['growth_percentage']),
                'unit' => '%',
                'target' => 10,
                'status' => $dashboard['revenue']['growth_percentage'] > 0 ? 'excellent' : 'needs_improvement',
                'color' => $dashboard['revenue']['growth_percentage'] > 0 ? 'green' : 'red'
            ]
        ];
    }

    /**
     * Actions rapides contextuelles
     */
    protected function generateQuickActions(array $dashboard): array
    {
        $actions = [
            [
                'id' => 'view_analytics',
                'title' => 'Analytics Détaillées',
                'icon' => 'chart-line',
                'url' => '/admin/analytics/detailed',
                'color' => 'blue'
            ],
            [
                'id' => 'export_data',
                'title' => 'Exporter Données',
                'icon' => 'download',
                'action' => 'export',
                'color' => 'green'
            ]
        ];

        // Actions contextuelles basées sur les données
        if ($dashboard['revenue']['growth_percentage'] < 0) {
            $actions[] = [
                'id' => 'boost_sales',
                'title' => 'Booster les Ventes',
                'icon' => 'rocket',
                'url' => '/admin/promotions/create',
                'color' => 'orange',
                'urgent' => true
            ];
        }

        if ($dashboard['orders']['completion_rate'] < 85) {
            $actions[] = [
                'id' => 'improve_orders',
                'title' => 'Améliorer Processus',
                'icon' => 'cogs',
                'url' => '/admin/orders/analysis',
                'color' => 'red',
                'urgent' => true
            ];
        }

        return $actions;
    }

    /**
     * Widgets d'alertes
     */
    protected function generateAlertWidgets(array $dashboard): array
    {
        $alerts = [];

        // Alerte croissance négative
        if ($dashboard['revenue']['growth_percentage'] < -10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Baisse de Chiffre d\'Affaires',
                'message' => "CA en baisse de {$dashboard['revenue']['growth_percentage']}%",
                'action' => 'Analyser les causes',
                'priority' => 'high'
            ];
        }

        // Alerte taux annulation
        if ($dashboard['orders']['completion_rate'] < 80) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Taux d\'Annulation Élevé',
                'message' => "Seulement {$dashboard['orders']['completion_rate']}% de commandes complétées",
                'action' => 'Vérifier qualité service',
                'priority' => 'critical'
            ];
        }

        // Alerte produits sous-performants
        $topProducts = $dashboard['products']['top_selling_products'];
        if (count($topProducts) > 0 && $topProducts[0]->total_sold < 10) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Faibles Ventes Produits',
                'message' => 'Le produit le plus vendu n\'a que ' . $topProducts[0]->total_sold . ' ventes',
                'action' => 'Réviser stratégie produits',
                'priority' => 'medium'
            ];
        }

        return $alerts;
    }

    /**
     * Formater données de tendance pour graphiques
     */
    protected function formatTrendData(array $trendData): array
    {
        return [
            'labels' => array_column($trendData, 'date'),
            'datasets' => [
                [
                    'label' => 'Chiffre d\'Affaires (€)',
                    'data' => array_column($trendData, 'revenue'),
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Commandes',
                    'data' => array_column($trendData, 'orders'),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'yAxisID' => 'y1'
                ]
            ]
        ];
    }

    /**
     * Formater données horaires
     */
    protected function formatHourlyData(array $hourlyData): array
    {
        $hours = range(0, 23);
        $data = [];

        foreach ($hours as $hour) {
            $data[] = $hourlyData[$hour] ?? 0;
        }

        return [
            'labels' => array_map(fn($h) => $h . 'h', $hours),
            'datasets' => [
                [
                    'label' => 'Revenue par Heure',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Formater données produits
     */
    protected function formatProductsData($products): array
    {
        $topProducts = collect($products)->take(5);

        return [
            'labels' => $topProducts->pluck('product_name')->toArray(),
            'datasets' => [
                [
                    'data' => $topProducts->pluck('total_sold')->toArray(),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                ]
            ]
        ];
    }

    /**
     * Formater pattern hebdomadaire
     */
    protected function formatWeeklyPattern(array $weeklyData): array
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'Commandes par Jour',
                    'data' => array_column($weeklyData, 'order_count'),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'pointBackgroundColor' => 'rgb(255, 99, 132)'
                ]
            ]
        ];
    }

    /**
     * Obtenir couleur selon tendance
     */
    protected function getTrendColor(string $trend): string
    {
        return match($trend) {
            'up' => 'green',
            'down' => 'red',
            'stable' => 'blue'
        };
    }

    /**
     * Calculer croissance des commandes
     */
    protected function calculateOrderGrowth(array $dashboard): float
    {
        // Placeholder - nécessite données période précédente
        return 0;
    }

    /**
     * Obtenir icône pour insight
     */
    protected function getInsightIcon(string $type): string
    {
        return match($type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle',
            'highlight' => 'star',
            default => 'lightbulb'
        };
    }

    /**
     * Obtenir priorité insight
     */
    protected function getInsightPriority(string $type): string
    {
        return match($type) {
            'success' => 'low',
            'warning' => 'high',
            'info' => 'medium',
            'highlight' => 'medium',
            default => 'low'
        };
    }
}
