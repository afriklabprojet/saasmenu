<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWhatsAppOrderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $whatsappService;

    /**
     * Create the event listener.
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        // Vérifier si les notifications WhatsApp sont activées
        if (!config('whatsapp.enabled') || !config('whatsapp.notifications.notify_restaurant_on_order')) {
            return;
        }

        try {
            // Envoyer la notification WhatsApp au restaurant
            $result = $this->whatsappService->sendOrderNotification(
                $event->order,
                $event->restaurant
            );

            if ($result['success']) {
                Log::info('WhatsApp order notification sent successfully', [
                    'order_id' => $event->order->id,
                    'restaurant_id' => $event->restaurant->id,
                    'message_id' => $result['message_id'] ?? null
                ]);
            } else {
                Log::warning('WhatsApp order notification failed', [
                    'order_id' => $event->order->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp order notification exception: ' . $e->getMessage());

            // Ne pas faire échouer la création de commande si WhatsApp échoue
            // L'échec est loggé mais n'impacte pas le flux principal
        }
    }

    /**
     * Déterminer le nombre de tentatives
     */
    public function tries()
    {
        return config('whatsapp.limits.max_retry_attempts', 3);
    }

    /**
     * Délai avant retry (en secondes)
     */
    public function backoff()
    {
        return config('whatsapp.limits.retry_delay', 60);
    }
}
