<?php

/**
 * EXEMPLE D'INTÉGRATION DES TEMPLATES WHATSAPP
 *
 * Ce fichier montre comment intégrer les templates WhatsApp
 * dans le workflow de commande existant.
 *
 * À intégrer dans : app/Http/Controllers/web/HomeController.php
 * ou créer un Event/Listener pour déclencher automatiquement
 */

namespace App\Examples;

use App\Models\Order;
use App\Services\WhatsAppTemplateService;
use App\Helpers\helper;
use Illuminate\Support\Facades\Log;

class WhatsAppIntegrationExample
{
    /**
     * EXEMPLE 1: Envoi lors de la création de commande
     *
     * À placer après la création de la commande dans paymentmethod()
     */
    public function sendNewOrderNotification($order_number, $vendor_id, $vendordata)
    {
        try {
            // Vérifier si les notifications automatiques sont activées
            if (!config('whatsapp-templates.auto_notifications.order_created', true)) {
                return false;
            }

            // Récupérer la commande
            $order = Order::where('order_number', $order_number)
                ->where('vendor_id', $vendor_id)
                ->first();

            if (!$order) {
                Log::error('Order not found for WhatsApp notification', [
                    'order_number' => $order_number
                ]);
                return false;
            }

            // Générer le message avec le nouveau service
            $message = WhatsAppTemplateService::generateNewOrderMessage(
                $order_number,
                $vendor_id,
                $vendordata
            );

            // Construire l'URL WhatsApp
            $whatsapp_url = "https://api.whatsapp.com/send";
            $whatsapp_url .= "?phone=" . $order->mobile;
            $whatsapp_url .= "&text=" . $message;

            // Logger pour analytics
            Log::info('WhatsApp message generated', [
                'order_number' => $order_number,
                'template' => 'new_order',
                'customer' => $order->customer_name,
                'url_length' => strlen($whatsapp_url)
            ]);

            // Retourner l'URL pour redirection ou affichage
            return $whatsapp_url;

        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', [
                'error' => $e->getMessage(),
                'order_number' => $order_number
            ]);
            return false;
        }
    }

    /**
     * EXEMPLE 2: Changement de statut de commande
     *
     * À intégrer dans le OrderController ou via Event Listener
     */
    public function handleOrderStatusChange($order, $new_status, $vendordata)
    {
        $message = null;
        $template_type = null;

        // Déterminer le template selon le statut
        switch ($new_status) {
            case 'Accepted':
                if (config('whatsapp-templates.auto_notifications.order_accepted', true)) {
                    $message = WhatsAppTemplateService::generateConfirmationMessage(
                        $order->order_number,
                        $order->vendor_id,
                        $vendordata
                    );
                    $template_type = 'order_confirmed';
                }
                break;

            case 'Preparing':
                if (config('whatsapp-templates.auto_notifications.order_preparing', true)) {
                    $message = WhatsAppTemplateService::generatePreparingMessage(
                        $order->order_number,
                        $order->vendor_id,
                        $vendordata
                    );
                    $template_type = 'order_preparing';
                }
                break;

            case 'Ready':
                if (config('whatsapp-templates.auto_notifications.order_ready', true)) {
                    $message = WhatsAppTemplateService::generateReadyMessage(
                        $order->order_number,
                        $order->vendor_id,
                        $vendordata
                    );
                    $template_type = 'order_ready';
                }
                break;

            case 'Cancelled':
                if (config('whatsapp-templates.auto_notifications.order_cancelled', true)) {
                    // Pour l'annulation, on utiliserait un template spécifique
                    // (à ajouter dans WhatsAppTemplateService si besoin)
                    $template_type = 'order_cancelled';
                }
                break;
        }

        // Envoyer si message généré
        if ($message) {
            return $this->sendWhatsAppMessage($order, $message, $template_type);
        }

        return false;
    }

    /**
     * EXEMPLE 3: Rappel de paiement automatique
     *
     * À déclencher via un Job/Queue après X minutes
     */
    public function sendPaymentReminder($order_number, $vendor_id, $vendordata, $payment_link)
    {
        try {
            // Vérifier si activé
            if (!config('whatsapp-templates.auto_notifications.payment_pending', false)) {
                return false;
            }

            $order = Order::where('order_number', $order_number)
                ->where('vendor_id', $vendor_id)
                ->where('payment_status', 'pending')
                ->first();

            if (!$order) {
                return false;
            }

            // Générer message de rappel
            $message = WhatsAppTemplateService::generatePaymentReminderMessage(
                $order_number,
                $vendor_id,
                $vendordata,
                $payment_link
            );

            return $this->sendWhatsAppMessage($order, $message, 'payment_reminder');

        } catch (\Exception $e) {
            Log::error('Payment reminder failed', [
                'error' => $e->getMessage(),
                'order_number' => $order_number
            ]);
            return false;
        }
    }

    /**
     * EXEMPLE 4: Message de bienvenue (chat WhatsApp)
     *
     * À utiliser dans le formulaire de contact WhatsApp
     */
    public function getWelcomeMessage($vendordata)
    {
        return WhatsAppTemplateService::generateWelcomeMessage($vendordata);
    }

    /**
     * Méthode utilitaire pour envoyer un message WhatsApp
     */
    private function sendWhatsAppMessage($order, $message, $template_type)
    {
        try {
            // Construire URL WhatsApp
            $whatsapp_url = "https://api.whatsapp.com/send";
            $whatsapp_url .= "?phone=" . $order->mobile;
            $whatsapp_url .= "&text=" . $message;

            // Logger
            Log::info('WhatsApp message sent', [
                'order_number' => $order->order_number,
                'template' => $template_type,
                'customer' => $order->customer_name,
                'mobile' => $order->mobile
            ]);

            // Retourner l'URL (ou rediriger directement)
            return $whatsapp_url;

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'error' => $e->getMessage(),
                'order' => $order->order_number,
                'template' => $template_type
            ]);
            return false;
        }
    }

    /**
     * EXEMPLE 5: Utilisation dans une Vue Blade
     *
     * resources/views/admin/orders/details.blade.php
     */
    public function getBladeExample()
    {
        return <<<'BLADE'
<!-- Bouton pour renvoyer une notification -->
<button onclick="resendWhatsAppNotification('{{ $order->order_number }}')"
        class="btn btn-success">
    <i class="fab fa-whatsapp"></i> Renvoyer Notification WhatsApp
</button>

<script>
function resendWhatsAppNotification(orderNumber) {
    fetch('/admin/orders/resend-whatsapp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_number: orderNumber,
            template: 'order_ready' // ou autre
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.whatsapp_url) {
            window.open(data.whatsapp_url, '_blank');
        }
        alert('Notification envoyée !');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'envoi');
    });
}
</script>
BLADE;
    }

    /**
     * EXEMPLE 6: Intégration avec Event/Listener Laravel
     *
     * Créer : app/Events/OrderStatusChanged.php
     * Créer : app/Listeners/SendWhatsAppNotification.php
     */
    public function getEventListenerExample()
    {
        return [
            'event' => <<<'PHP'
<?php
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public $order;
    public $old_status;
    public $new_status;

    public function __construct(Order $order, $old_status, $new_status)
    {
        $this->order = $order;
        $this->old_status = $old_status;
        $this->new_status = $new_status;
    }
}
PHP,

            'listener' => <<<'PHP'
<?php
namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\WhatsAppTemplateService;
use App\Helpers\helper;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification
{
    public function handle(OrderStatusChanged $event)
    {
        $order = $event->order;
        $status = $event->new_status;

        // Récupérer les données du restaurant
        $vendordata = \App\Models\User::find($order->vendor_id);

        $message = null;

        // Générer le bon message selon le statut
        switch ($status) {
            case 'Accepted':
                $message = WhatsAppTemplateService::generateConfirmationMessage(
                    $order->order_number,
                    $order->vendor_id,
                    $vendordata
                );
                break;

            case 'Preparing':
                $message = WhatsAppTemplateService::generatePreparingMessage(
                    $order->order_number,
                    $order->vendor_id,
                    $vendordata
                );
                break;

            case 'Ready':
                $message = WhatsAppTemplateService::generateReadyMessage(
                    $order->order_number,
                    $order->vendor_id,
                    $vendordata
                );
                break;
        }

        if ($message) {
            // Construire URL WhatsApp
            $url = "https://api.whatsapp.com/send?phone={$order->mobile}&text={$message}";

            // Logger ou envoyer automatiquement
            Log::info('WhatsApp auto-notification', [
                'order' => $order->order_number,
                'status' => $status
            ]);
        }
    }
}
PHP,

            'register' => <<<'PHP'
// Dans app/Providers/EventServiceProvider.php

protected $listen = [
    \App\Events\OrderStatusChanged::class => [
        \App\Listeners\SendWhatsAppNotification::class,
    ],
];
PHP
        ];
    }

    /**
     * EXEMPLE 7: Job en queue pour rappel de paiement
     *
     * php artisan make:job SendPaymentReminder
     */
    public function getJobExample()
    {
        return <<<'PHP'
<?php
namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $payment_link;

    public function __construct(Order $order, $payment_link)
    {
        $this->order = $order;
        $this->payment_link = $payment_link;
    }

    public function handle()
    {
        $vendordata = \App\Models\User::find($this->order->vendor_id);

        $message = WhatsAppTemplateService::generatePaymentReminderMessage(
            $this->order->order_number,
            $this->order->vendor_id,
            $vendordata,
            $this->payment_link
        );

        // Envoyer le message (API WhatsApp Business ou lien direct)
        $url = "https://api.whatsapp.com/send?phone={$this->order->mobile}&text={$message}";

        // Logger
        \Log::info('Payment reminder sent', [
            'order' => $this->order->order_number
        ]);
    }
}

// Dispatcher le job avec délai :
// SendPaymentReminder::dispatch($order, $payment_link)->delay(now()->addMinutes(15));
PHP;
    }
}
