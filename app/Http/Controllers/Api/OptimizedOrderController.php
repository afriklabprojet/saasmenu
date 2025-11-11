<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DeferredExecutionService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OptimizedOrderController extends Controller
{
    protected DeferredExecutionService $deferredService;

    public function __construct(DeferredExecutionService $deferredService)
    {
        $this->deferredService = $deferredService;
    }

    /**
     * Créer une commande avec traitement différé optimisé
     * DÉMONSTRATION: Performance équivalente aux deferred functions Laravel 12
     */
    public function store(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        // 1. TRAITEMENT CRITIQUE IMMÉDIAT (security fix: protected fields set separately)
        $order = Order::create([
            'vendor_id' => $request->vendor_id,
            'customer_id' => $request->customer_id,
        ]);
        
        // Set protected fields
        $order->total = $request->total;
        $order->status = 'pending';
        $order->save();

        // 2. RÉPONSE IMMÉDIATE À L'UTILISATEUR (comme Laravel 12 deferred)
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        // 3. TRAITEMENTS DIFFÉRÉS (exécution background)
        $this->schedulePostOrderTasks($order);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Commande créée avec succès',
            'response_time_ms' => $responseTime,
            'deferred_tasks' => 'scheduled'
        ], 201);
    }

    /**
     * Programmer les tâches post-commande en arrière-plan
     */
    private function schedulePostOrderTasks(Order $order): void
    {
        // NOTIFICATION WHATSAPP (priorité haute - 0 délai)
        deferWhatsApp(['order_id' => $order->id]);

        // EMAIL CONFIRMATION (priorité normale)
        deferEmail(['order_id' => $order->id]);

        // ANALYTICS & TRACKING (priorité normale)
        deferAnalytics(['order_id' => $order->id]);

        // CACHE WARMING (priorité basse - délai 5s)
        defer('cache_warming', ['vendor_id' => $order->vendor_id], 5, 'cache');

        Log::info('Post-order tasks scheduled', [
            'order_id' => $order->id,
            'tasks' => ['whatsapp', 'email', 'analytics', 'cache']
        ]);
    }

    /**
     * Simuler envoi WhatsApp (normalement 1-2 secondes)
     */
    private function sendWhatsAppNotification(Order $order): void
    {
        // Simulation d'envoi WhatsApp
        sleep(1); // Simule 1 seconde d'envoi

        Log::info('WhatsApp notification sent', [
            'order_id' => $order->id,
            'customer_phone' => $order->customer->phone ?? 'N/A'
        ]);
    }

    /**
     * Simuler envoi email (normalement 0.5-1 seconde)
     */
    private function sendOrderConfirmationEmail(Order $order): void
    {
        // Simulation d'envoi email
        usleep(500000); // 0.5 seconde

        Log::info('Order confirmation email sent', [
            'order_id' => $order->id,
            'customer_email' => $order->customer->email ?? 'N/A'
        ]);
    }

    /**
     * Traiter analytics (normalement 0.3-0.8 secondes)
     */
    private function trackOrderAnalytics(Order $order): void
    {
        // Simulation analytics
        usleep(300000); // 0.3 seconde

        Log::info('Order analytics tracked', [
            'order_id' => $order->id,
            'analytics' => ['revenue', 'conversion', 'customer_behavior']
        ]);
    }

    /**
     * Mettre à jour métriques business
     */
    private function updateBusinessMetrics(Order $order): void
    {
        // Mise à jour métriques
        Log::info('Business metrics updated', [
            'order_id' => $order->id,
            'metrics' => ['daily_revenue', 'order_count', 'avg_order_value']
        ]);
    }

    /**
     * Réchauffer les caches liés
     */
    private function warmupRelatedCaches(Order $order): void
    {
        // Cache warming
        Log::info('Related caches warmed up', [
            'order_id' => $order->id,
            'caches' => ['vendor_menu', 'popular_items', 'customer_history']
        ]);
    }

    /**
     * Statistiques du système de queues
     */
    public function queueStats(): JsonResponse
    {
        $stats = $this->deferredService->getStats();

        return response()->json([
            'queue_system' => 'Laravel 10 Background Jobs',
            'performance_equivalent' => 'Laravel 12 Deferred Functions',
            'stats' => $stats
        ]);
    }
}
