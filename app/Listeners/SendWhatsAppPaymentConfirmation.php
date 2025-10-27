<?php

namespace App\Listeners;

use App\Events\PaymentConfirmedEvent;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWhatsAppPaymentConfirmation implements ShouldQueue
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
    public function handle(PaymentConfirmedEvent $event): void
    {
        if (!config('whatsapp.enabled') || !config('whatsapp.notifications.notify_customer_on_payment')) {
            return;
        }

        try {
            $result = $this->whatsappService->sendPaymentConfirmation(
                $event->order,
                $event->customer
            );

            if ($result['success']) {
                Log::info('WhatsApp payment confirmation sent', [
                    'order_id' => $event->order->id,
                    'customer_id' => $event->customer->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp payment confirmation exception: ' . $e->getMessage());
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
