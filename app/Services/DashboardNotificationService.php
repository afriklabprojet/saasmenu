<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Events\DashboardMetricUpdated;
use Carbon\Carbon;

/**
 * Service de notification en temps réel pour les widgets dashboard
 */
class DashboardNotificationService
{
    protected BusinessIntelligenceService $biService;
    protected DashboardWidgetService $widgetService;

    public function __construct(
        BusinessIntelligenceService $biService,
        DashboardWidgetService $widgetService
    ) {
        $this->biService = $biService;
        $this->widgetService = $widgetService;
    }

    /**
     * Mettre à jour les métriques en temps réel
     */
    public function updateRealTimeMetrics(int $vendorId, string $eventType, array $data = []): array
    {
        $cacheKey = "realtime_metrics_{$vendorId}";
        $currentMetrics = Cache::get($cacheKey, []);

        // Mise à jour selon le type d'événement
        switch ($eventType) {
            case 'new_order':
                $currentMetrics = $this->handleNewOrder($vendorId, $data, $currentMetrics);
                break;

            case 'order_completed':
                $currentMetrics = $this->handleOrderCompleted($vendorId, $data, $currentMetrics);
                break;

            case 'order_cancelled':
                $currentMetrics = $this->handleOrderCancelled($vendorId, $data, $currentMetrics);
                break;

            case 'payment_received':
                $currentMetrics = $this->handlePaymentReceived($vendorId, $data, $currentMetrics);
                break;

            case 'new_customer':
                $currentMetrics = $this->handleNewCustomer($vendorId, $data, $currentMetrics);
                break;

            default:
                $currentMetrics = $this->refreshAllMetrics($vendorId);
        }

        // Sauvegarder en cache avec TTL courte pour temps réel
        Cache::put($cacheKey, $currentMetrics, 300); // 5 minutes

        // Émettre événement pour notification WebSocket
        Event::dispatch(new DashboardMetricUpdated($vendorId, $eventType, $currentMetrics));

        return $currentMetrics;
    }

    /**
     * Gérer nouvelle commande
     */
    protected function handleNewOrder(int $vendorId, array $data, array $currentMetrics): array
    {
        $orderValue = $data['order_total'] ?? 0;

        // Mise à jour compteurs temps réel
        $currentMetrics['active_orders'] = ($currentMetrics['active_orders'] ?? 0) + 1;
        $currentMetrics['today_orders'] = ($currentMetrics['today_orders'] ?? 0) + 1;
        $currentMetrics['last_order_time'] = now()->format('H:i:s');

        // Mise à jour revenue potentiel
        $currentMetrics['pending_revenue'] = ($currentMetrics['pending_revenue'] ?? 0) + $orderValue;

        // Analytics en arrière-plan
        app(\App\Services\DeferredExecutionService::class)->defer('analytics_tracking', [
            'vendor_id' => $vendorId,
            'event' => 'new_order',
            'data' => $data
        ]);

        return $currentMetrics;
    }

    /**
     * Gérer commande complétée
     */
    protected function handleOrderCompleted(int $vendorId, array $data, array $currentMetrics): array
    {
        $orderValue = $data['order_total'] ?? 0;

        // Mise à jour compteurs
        $currentMetrics['active_orders'] = max(0, ($currentMetrics['active_orders'] ?? 1) - 1);
        $currentMetrics['today_revenue'] = ($currentMetrics['today_revenue'] ?? 0) + $orderValue;
        $currentMetrics['completed_orders'] = ($currentMetrics['completed_orders'] ?? 0) + 1;

        // Enlever du pending
        $currentMetrics['pending_revenue'] = max(0, ($currentMetrics['pending_revenue'] ?? 0) - $orderValue);

        // Calculer nouveau panier moyen en temps réel
        if ($currentMetrics['completed_orders'] > 0) {
            $currentMetrics['avg_order_value'] = $currentMetrics['today_revenue'] / $currentMetrics['completed_orders'];
        }

        // Analytics en arrière-plan
        app(\App\Services\DeferredExecutionService::class)->defer('analytics_tracking', [
            'vendor_id' => $vendorId,
            'event' => 'order_completed',
            'data' => $data
        ]);

        return $currentMetrics;
    }

    /**
     * Gérer commande annulée
     */
    protected function handleOrderCancelled(int $vendorId, array $data, array $currentMetrics): array
    {
        $orderValue = $data['order_total'] ?? 0;

        // Mise à jour compteurs
        $currentMetrics['active_orders'] = max(0, ($currentMetrics['active_orders'] ?? 1) - 1);
        $currentMetrics['cancelled_orders'] = ($currentMetrics['cancelled_orders'] ?? 0) + 1;

        // Enlever du pending revenue
        $currentMetrics['pending_revenue'] = max(0, ($currentMetrics['pending_revenue'] ?? 0) - $orderValue);

        // Recalculer taux de succès
        $totalOrders = $currentMetrics['completed_orders'] + $currentMetrics['cancelled_orders'];
        if ($totalOrders > 0) {
            $currentMetrics['success_rate'] = ($currentMetrics['completed_orders'] / $totalOrders) * 100;
        }

        // Analytics en arrière-plan
        app(\App\Services\DeferredExecutionService::class)->defer('analytics_tracking', [
            'vendor_id' => $vendorId,
            'event' => 'order_cancelled',
            'data' => $data
        ]);

        return $currentMetrics;
    }

    /**
     * Gérer paiement reçu
     */
    protected function handlePaymentReceived(int $vendorId, array $data, array $currentMetrics): array
    {
        $amount = $data['amount'] ?? 0;

        $currentMetrics['today_revenue'] = ($currentMetrics['today_revenue'] ?? 0) + $amount;
        $currentMetrics['payments_received'] = ($currentMetrics['payments_received'] ?? 0) + 1;
        $currentMetrics['last_payment_time'] = now()->format('H:i:s');

        // Analytics en arrière-plan
        app(\App\Services\DeferredExecutionService::class)->defer('analytics_tracking', [
            'vendor_id' => $vendorId,
            'event' => 'payment_received',
            'data' => $data
        ]);

        return $currentMetrics;
    }

    /**
     * Gérer nouveau client
     */
    protected function handleNewCustomer(int $vendorId, array $data, array $currentMetrics): array
    {
        $currentMetrics['new_customers_today'] = ($currentMetrics['new_customers_today'] ?? 0) + 1;
        $currentMetrics['online_customers'] = ($currentMetrics['online_customers'] ?? 0) + 1;

        // Analytics en arrière-plan
        app(\App\Services\DeferredExecutionService::class)->defer('analytics_tracking', [
            'vendor_id' => $vendorId,
            'event' => 'new_customer',
            'data' => $data
        ]);

        return $currentMetrics;
    }

    /**
     * Rafraîchir toutes les métriques
     */
    protected function refreshAllMetrics(int $vendorId): array
    {
        // Obtenir métriques fraîches de la base de données
        return [
            'active_orders' => \App\Models\Order::where('vendor_id', $vendorId)
                ->whereIn('status', [1, 2]) // 1=placed, 2=confirmed
                ->count(),
            'today_revenue' => \App\Models\Order::where('vendor_id', $vendorId)
                ->whereDate('created_at', today())
                ->where('status', 5) // 5=delivered
                ->sum('grand_total'),
            'today_orders' => \App\Models\Order::where('vendor_id', $vendorId)
                ->whereDate('created_at', today())
                ->count(),
            'completed_orders' => \App\Models\Order::where('vendor_id', $vendorId)
                ->whereDate('created_at', today())
                ->where('status', 5) // 5=delivered
                ->count(),
            'cancelled_orders' => \App\Models\Order::where('vendor_id', $vendorId)
                ->whereDate('created_at', today())
                ->whereIn('status', [3, 4]) // 3=cancelled by admin, 4=cancelled by user
                ->count(),
            'online_customers' => 0, // À implémenter avec sessions
            'server_status' => 'online',
            'last_order_time' => \App\Models\Order::where('vendor_id', $vendorId)
                ->latest()
                ->first()?->created_at?->format('H:i:s') ?? 'Aucune',
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Obtenir snapshot complet du dashboard
     */
    public function getDashboardSnapshot(int $vendorId): array
    {
        $cacheKey = "dashboard_snapshot_{$vendorId}";

        return Cache::remember($cacheKey, 1800, function() use ($vendorId) {
            return [
                'realtime_metrics' => $this->updateRealTimeMetrics($vendorId, 'refresh'),
                'widgets' => $this->widgetService->generateWidgets($vendorId, 'today'),
                'alerts' => $this->generateActiveAlerts($vendorId),
                'system_status' => $this->getSystemStatus(),
                'last_updated' => now()->toDateTimeString()
            ];
        });
    }

    /**
     * Générer alertes actives
     */
    protected function generateActiveAlerts(int $vendorId): array
    {
        $alerts = [];

        // Vérifier métriques critiques
        $metrics = $this->updateRealTimeMetrics($vendorId, 'refresh');

        // Alerte commandes en attente trop nombreuses
        if (($metrics['active_orders'] ?? 0) > 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Nombreuses commandes en attente',
                'message' => "{$metrics['active_orders']} commandes nécessitent votre attention",
                'priority' => 'high',
                'action_url' => '/admin/orders?status=pending'
            ];
        }

        // Alerte taux d'annulation élevé
        $totalOrders = ($metrics['completed_orders'] ?? 0) + ($metrics['cancelled_orders'] ?? 0);
        if ($totalOrders > 5) {
            $cancellationRate = (($metrics['cancelled_orders'] ?? 0) / $totalOrders) * 100;
            if ($cancellationRate > 20) {
                $alerts[] = [
                    'type' => 'error',
                    'title' => 'Taux d\'annulation élevé',
                    'message' => "Taux d'annulation: " . round($cancellationRate, 1) . '%',
                    'priority' => 'critical',
                    'action_url' => '/admin/analytics/orders'
                ];
            }
        }

        // Alerte revenus faibles
        $expectedRevenue = $this->getExpectedDailyRevenue($vendorId);
        if ($expectedRevenue > 0 && ($metrics['today_revenue'] ?? 0) < ($expectedRevenue * 0.5)) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Revenus en dessous des attentes',
                'message' => 'CA journalier inférieur à la moyenne habituelle',
                'priority' => 'medium',
                'action_url' => '/admin/promotions'
            ];
        }

        return $alerts;
    }

    /**
     * Obtenir status système
     */
    protected function getSystemStatus(): array
    {
        return [
            'app_status' => 'online',
            'database_status' => $this->checkDatabaseConnection(),
            'cache_status' => $this->checkCacheConnection(),
            'queue_status' => $this->checkQueueStatus(),
            'last_check' => now()->toDateTimeString()
        ];
    }

    /**
     * Vérifier connexion base de données
     */
    protected function checkDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();
            return 'online';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    /**
     * Vérifier connexion cache
     */
    protected function checkCacheConnection(): string
    {
        try {
            Cache::put('system_check', 'ok', 10);
            return Cache::get('system_check') === 'ok' ? 'online' : 'offline';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    /**
     * Vérifier status des queues
     */
    protected function checkQueueStatus(): string
    {
        try {
            // Vérifier si les workers sont actifs
            $queueSize = Queue::size();
            return $queueSize < 100 ? 'online' : 'overloaded';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    /**
     * Tracker événement de commande
     */
    protected function trackOrderEvent(int $vendorId, string $event, array $data): void
    {
        // Implémenter tracking détaillé
        // Peut utiliser le système d'analytics ou logs spécialisés
    }

    /**
     * Tracker événement de paiement
     */
    protected function trackPaymentEvent(int $vendorId, array $data): void
    {
        // Implémenter tracking des paiements
    }

    /**
     * Tracker événement client
     */
    protected function trackCustomerEvent(int $vendorId, string $event, array $data): void
    {
        // Implémenter tracking des clients
    }

    /**
     * Mettre à jour statistiques journalières
     */
    protected function updateDailyStats(int $vendorId): void
    {
        // Mettre à jour les agrégations journalières
        $cacheKey = "daily_stats_{$vendorId}_" . today()->format('Y-m-d');
        Cache::forget($cacheKey);
    }

    /**
     * Obtenir revenus journaliers attendus
     */
    protected function getExpectedDailyRevenue(int $vendorId): float
    {
        // Calculer moyenne des 30 derniers jours
        return \App\Models\Order::where('vendor_id', $vendorId)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', 5) // 5=delivered
            ->avg('grand_total') ?? 0;
    }
}
