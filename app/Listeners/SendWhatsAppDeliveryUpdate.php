<?php

namespace App\Listeners;

use App\Events\DeliveryStatusUpdatedEvent;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWhatsAppDeliveryUpdate implements ShouldQueue
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
    public function handle(DeliveryStatusUpdatedEvent $event): void
    {
        if (!config('whatsapp.enabled') || !config('whatsapp.notifications.notify_customer_on_delivery_update')) {
            return;
        }

        try {
            $result = $this->whatsappService->sendDeliveryUpdate(
                $event->order,
                $event->customer,
                $event->status
            );

            if ($result['success']) {
                Log::info('WhatsApp delivery update sent', [
                    'order_id' => $event->order->id,
                    'status' => $event->status
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp delivery update exception: ' . $e->getMessage());
        }
    }

    public function tries()
    {
        return config('whatsapp.limits.max_retry_attempts', 3);
    }

    public function backoff()
    {
        return config('whatsapp.limits.retry_delay', 60);
    }
}
