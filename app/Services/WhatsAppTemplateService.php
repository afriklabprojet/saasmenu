<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Helpers\helper;
use Illuminate\Support\Facades\URL;

/**
 * Service de gestion des templates de messages WhatsApp
 *
 * Ce service centralise la création de messages WhatsApp optimisés
 * pour différents événements du cycle de vie d'une commande
 */
class WhatsAppTemplateService
{
    /**
     * Templates de messages optimisés par type
     */
    private const TEMPLATES = [
        'new_order' => [
            'emoji' => '🎉',
            'title' => 'Nouvelle Commande',
            'format' => 'structured'
        ],
        'order_confirmed' => [
            'emoji' => '✅',
            'title' => 'Commande Confirmée',
            'format' => 'simple'
        ],
        'order_preparing' => [
            'emoji' => '👨‍🍳',
            'title' => 'Préparation en Cours',
            'format' => 'simple'
        ],
        'order_ready' => [
            'emoji' => '✨',
            'title' => 'Commande Prête',
            'format' => 'simple'
        ],
        'order_delivered' => [
            'emoji' => '🎊',
            'title' => 'Livraison Effectuée',
            'format' => 'simple'
        ],
        'order_cancelled' => [
            'emoji' => '❌',
            'title' => 'Commande Annulée',
            'format' => 'simple'
        ],
        'payment_reminder' => [
            'emoji' => '💳',
            'title' => 'Rappel de Paiement',
            'format' => 'payment'
        ]
    ];

    /**
     * Génère un message WhatsApp optimisé pour une nouvelle commande
     *
     * @param string $order_number Numéro de commande
     * @param int $vdata ID du vendeur
     * @param object $vendordata Données du restaurant
     * @return string Message formaté pour WhatsApp
     */
    public static function generateNewOrderMessage($order_number, $vdata, $vendordata)
    {
        $order = Order::where('order_number', $order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return self::generateErrorMessage('Commande introuvable');
        }

        $template = self::TEMPLATES['new_order'];

        // Header
        $message = self::formatHeader($template['emoji'], $template['title']);
        $message .= self::formatOrderBasics($order, $vendordata);
        $message .= self::formatOrderItems($order, $vdata);
        $message .= self::formatOrderSummary($order, $vdata);
        $message .= self::formatDeliveryInfo($order);
        $message .= self::formatPaymentInfo($order, $vdata);
        $message .= self::formatFooter($order_number, $vendordata);

        return self::encodeForWhatsApp($message);
    }

    /**
     * Génère un message de confirmation de commande
     */
    public static function generateConfirmationMessage($order_number, $vdata, $vendordata)
    {
        $order = Order::where('order_number', $order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return self::generateErrorMessage('Commande introuvable');
        }

        $template = self::TEMPLATES['order_confirmed'];

        $message = self::formatHeader($template['emoji'], $template['title']);
        $message .= "\n";
        $message .= "Bonjour *{$order->customer_name}* !\n\n";
        $message .= "Votre commande *#{$order_number}* a été confirmée.\n\n";
        $message .= "📦 *Détails* :\n";
        $message .= "• Restaurant: {$vendordata->name}\n";
        $message .= "• Total: " . helper::currency_formate($order->grand_total, $vdata) . "\n";
        $message .= "• Livraison prévue: {$order->delivery_date} à {$order->delivery_time}\n\n";
        $message .= "📍 Suivez votre commande:\n";
        $message .= URL::to($vendordata->slug . "/track-order/" . $order_number) . "\n\n";
        $message .= "Merci de votre confiance ! 🙏";

        return self::encodeForWhatsApp($message);
    }

    /**
     * Génère un message de commande en préparation
     */
    public static function generatePreparingMessage($order_number, $vdata, $vendordata)
    {
        $order = Order::where('order_number', $order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return self::generateErrorMessage('Commande introuvable');
        }

        $template = self::TEMPLATES['order_preparing'];

        $message = self::formatHeader($template['emoji'], $template['title']);
        $message .= "\n";
        $message .= "Bonjour *{$order->customer_name}* !\n\n";
        $message .= "Bonne nouvelle ! Notre chef prépare votre commande *#{$order_number}* avec soin.\n\n";
        $message .= "⏱️ *Temps estimé* : 20-30 minutes\n\n";
        $message .= "Vous serez notifié dès que votre commande sera prête.\n\n";
        $message .= "📍 Suivi en temps réel:\n";
        $message .= URL::to($vendordata->slug . "/track-order/" . $order_number);

        return self::encodeForWhatsApp($message);
    }

    /**
     * Génère un message de commande prête
     */
    public static function generateReadyMessage($order_number, $vdata, $vendordata)
    {
        $order = Order::where('order_number', $order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return self::generateErrorMessage('Commande introuvable');
        }

        $template = self::TEMPLATES['order_ready'];

        $message = self::formatHeader($template['emoji'], $template['title']);
        $message .= "\n";
        $message .= "Bonjour *{$order->customer_name}* !\n\n";
        $message .= "Votre commande *#{$order_number}* est prête ! 🎉\n\n";

        if ($order->order_type == 1) {
            $message .= "🚗 *Livraison en cours*\n";
            $message .= "Notre livreur est en route vers :\n";
            $message .= "📍 {$order->address}\n";
            if ($order->building) {
                $message .= "🏢 {$order->building}\n";
            }
            if ($order->landmark) {
                $message .= "🗺️ Repère: {$order->landmark}\n";
            }
            $message .= "\nMerci de rester disponible au {$order->mobile}\n";
        } else {
            $message .= "🏪 *Retrait au restaurant*\n";
            $message .= "Vous pouvez venir récupérer votre commande chez :\n";
            $message .= "📍 {$vendordata->name}\n";
            $message .= "☎️ Contact: {$vendordata->mobile}\n";
        }

        $message .= "\nBon appétit ! 🍽️";

        return self::encodeForWhatsApp($message);
    }

    /**
     * Génère un message de rappel de paiement
     */
    public static function generatePaymentReminderMessage($order_number, $vdata, $vendordata, $payment_link = null)
    {
        $order = Order::where('order_number', $order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return self::generateErrorMessage('Commande introuvable');
        }

        $template = self::TEMPLATES['payment_reminder'];

        $message = self::formatHeader($template['emoji'], $template['title']);
        $message .= "\n";
        $message .= "Bonjour *{$order->customer_name}* !\n\n";
        $message .= "Votre commande *#{$order_number}* est en attente de paiement.\n\n";
        $message .= "💰 *Montant à payer* : " . helper::currency_formate($order->grand_total, $vdata) . "\n\n";

        if ($payment_link) {
            $message .= "💳 *Payer maintenant* :\n";
            $message .= $payment_link . "\n\n";
        }

        $message .= "📞 Besoin d'aide ? Contactez-nous :\n";
        $message .= $vendordata->mobile;

        return self::encodeForWhatsApp($message);
    }

    /**
     * Formate le header du message
     */
    private static function formatHeader($emoji, $title)
    {
        return "{$emoji} *{$title}* {$emoji}\n" . str_repeat("═", 30) . "\n";
    }

    /**
     * Formate les informations de base de la commande
     */
    private static function formatOrderBasics($order, $vendordata)
    {
        $order_type = $order->order_type == 1 ? '🚗 Livraison' : '🏪 Retrait';

        $message = "\n";
        $message .= "👤 *Client* : {$order->customer_name}\n";
        $message .= "📱 *Téléphone* : {$order->mobile}\n";
        $message .= "🏪 *Restaurant* : {$vendordata->name}\n";
        $message .= "📦 *Commande* : #{$order->order_number}\n";
        $message .= "🎯 *Type* : {$order_type}\n";
        $message .= "📅 *Date* : {$order->delivery_date} à {$order->delivery_time}\n";

        return $message;
    }

    /**
     * Formate la liste des articles
     */
    private static function formatOrderItems($order, $vdata)
    {
        $message = "\n🛒 *Articles Commandés* :\n" . str_repeat("─", 30) . "\n";

        $orderDetails = OrderDetails::where('order_id', $order->id)->get();

        foreach ($orderDetails as $index => $item) {
            $item_num = $index + 1;
            $variants_text = "";
            $item_total = 0;

            if ($item->variants_id != "") {
                $item_total = $item->qty * $item->variants_price;
                $variants_text = " ({$item->variants_name})";
            } else {
                $item_total = $item->qty * $item->price;
            }

            $message .= "\n{$item_num}. *{$item->item_name}*{$variants_text}\n";
            $message .= "   Quantité: {$item->qty} x " . helper::currency_formate($item_total / $item->qty, $vdata);
            $message .= " = " . helper::currency_formate($item_total, $vdata) . "\n";

            // Extras/Add-ons
            if ($item->extras_id != "") {
                $extras_id = explode("|", $item->extras_id);
                $extras_name = explode("|", $item->extras_name);
                $extras_price = explode("|", $item->extras_price);

                foreach ($extras_id as $key => $addon) {
                    $message .= "   ➕ {$extras_name[$key]}: " . helper::currency_formate($extras_price[$key], $vdata) . "\n";
                }
            }
        }

        return $message;
    }

    /**
     * Formate le résumé financier
     */
    private static function formatOrderSummary($order, $vdata)
    {
        $message = "\n" . str_repeat("─", 30) . "\n";
        $message .= "💰 *Résumé Financier* :\n\n";
        $message .= "• Sous-total : " . helper::currency_formate($order->sub_total, $vdata) . "\n";

        // Taxes
        if ($order->tax && $order->tax != "") {
            $taxes = explode("|", $order->tax);
            $tax_names = explode("|", $order->tax_name);

            foreach ($taxes as $key => $tax) {
                if ($tax > 0) {
                    $message .= "• {$tax_names[$key]} : " . helper::currency_formate($tax, $vdata) . "\n";
                }
            }
        }

        // Frais de livraison
        if ($order->delivery_charge > 0) {
            $message .= "• Frais de livraison : " . helper::currency_formate($order->delivery_charge, $vdata) . "\n";
        }

        // Réduction
        if ($order->discount_amount > 0) {
            $message .= "• 🎁 Réduction : -" . helper::currency_formate($order->discount_amount, $vdata) . "\n";
        }

        $message .= "\n" . str_repeat("═", 30) . "\n";
        $message .= "🎯 *TOTAL* : " . helper::currency_formate($order->grand_total, $vdata) . "\n";
        $message .= str_repeat("═", 30) . "\n";

        return $message;
    }

    /**
     * Formate les informations de livraison
     */
    private static function formatDeliveryInfo($order)
    {
        if ($order->order_type != 1) {
            return ""; // Pas de livraison pour les retraits
        }

        $message = "\n📍 *Adresse de Livraison* :\n";
        $message .= $order->address . "\n";

        if ($order->building) {
            $message .= "🏢 Bâtiment: {$order->building}\n";
        }

        if ($order->landmark) {
            $message .= "🗺️ Repère: {$order->landmark}\n";
        }

        if ($order->postal_code) {
            $message .= "📮 Code postal: {$order->postal_code}\n";
        }

        if ($order->order_notes) {
            $message .= "\n📝 *Notes* : {$order->order_notes}\n";
        }

        return $message;
    }

    /**
     * Formate les informations de paiement
     */
    private static function formatPaymentInfo($order, $vdata)
    {
        $payment_method = helper::getpayment($order->payment_type, $vdata)->payment_name ?? 'N/A';

        $message = "\n💳 *Mode de Paiement* : {$payment_method}\n";

        return $message;
    }

    /**
     * Formate le footer avec liens
     */
    private static function formatFooter($order_number, $vendordata)
    {
        $track_url = URL::to($vendordata->slug . "/track-order/" . $order_number);
        $store_url = URL::to($vendordata->slug);

        $message = "\n" . str_repeat("─", 30) . "\n";
        $message .= "📱 *Suivi en Temps Réel* :\n";
        $message .= $track_url . "\n\n";
        $message .= "🏪 *Voir le Menu* :\n";
        $message .= $store_url . "\n\n";
        $message .= "Merci de votre confiance ! 🙏\n";
        $message .= "_Envoyé par {$vendordata->name}_";

        return $message;
    }

    /**
     * Encode le message pour WhatsApp
     */
    private static function encodeForWhatsApp($message)
    {
        // Remplace les retours à la ligne par le format WhatsApp
        return str_replace("\n", "%0a", $message);
    }

    /**
     * Message d'erreur
     */
    private static function generateErrorMessage($error)
    {
        $message = "❌ *Erreur* ❌\n\n";
        $message .= $error . "\n\n";
        $message .= "Veuillez contacter le support.";

        return self::encodeForWhatsApp($message);
    }

    /**
     * Génère un message de bienvenue pour le chat WhatsApp
     */
    public static function generateWelcomeMessage($vendordata)
    {
        $message = "👋 *Bienvenue chez {$vendordata->name}* !\n\n";
        $message .= "Comment puis-je vous aider aujourd'hui ?\n\n";
        $message .= "🍽️ Consulter le menu\n";
        $message .= "📦 Passer une commande\n";
        $message .= "📍 Nous trouver\n";
        $message .= "☎️ Nous contacter\n\n";
        $message .= "Répondez avec le numéro de votre choix ou décrivez votre besoin.";

        return self::encodeForWhatsApp($message);
    }

    /**
     * Retourne tous les templates disponibles
     */
    public static function getAvailableTemplates()
    {
        return array_keys(self::TEMPLATES);
    }

    /**
     * Vérifie si un template existe
     */
    public static function templateExists($template_name)
    {
        return isset(self::TEMPLATES[$template_name]);
    }
}
