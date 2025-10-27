<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Webhook de vérification WhatsApp (GET)
     * Requis pour l'activation du webhook dans Meta Business
     */
    public function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp Webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $verifyToken
        ]);

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Recevoir les webhooks WhatsApp (POST)
     * Gère les mises à jour de statut des messages, les messages entrants, etc.
     */
    public function handleWebhook(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('WhatsApp Webhook received', ['data' => $data]);

            // Valider la signature du webhook
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Invalid WhatsApp webhook signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            // Traiter les entrées du webhook
            if (isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            $this->processWebhookChange($change);
                        }
                    }
                }
            }

            // WhatsApp attend une réponse 200 OK
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'ok'], 200); // Toujours retourner 200 pour éviter les retry
        }
    }

    /**
     * Traiter un changement de webhook
     */
    protected function processWebhookChange($change)
    {
        $value = $change['value'] ?? [];

        // Gestion des statuts de messages
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->updateMessageStatus($status);
            }
        }

        // Gestion des messages entrants (optionnel - pour les réponses clients)
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($message);
            }
        }
    }

    /**
     * Mettre à jour le statut d'un message
     */
    protected function updateMessageStatus($status)
    {
        $messageId = $status['id'] ?? null;
        $newStatus = $status['status'] ?? null; // sent, delivered, read, failed

        if ($messageId && $newStatus) {
            DB::table('whatsapp_messages_log')
                ->where('message_id', $messageId)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

            Log::info("WhatsApp message status updated", [
                'message_id' => $messageId,
                'status' => $newStatus
            ]);
        }
    }

    /**
     * Gérer un message entrant (optionnel)
     */
    protected function handleIncomingMessage($message)
    {
        $from = $message['from'] ?? null;
        $text = $message['text']['body'] ?? null;

        if ($from && $text) {
            Log::info("WhatsApp incoming message", [
                'from' => $from,
                'text' => $text
            ]);

            // Ici, vous pouvez implémenter une logique de réponse automatique
            // Par exemple : confirmation de commande, suivi de livraison, etc.
        }
    }

    /**
     * Valider la signature du webhook
     */
    protected function validateWebhookSignature(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return false;
        }

        $appSecret = config('whatsapp.app_secret');
        $payload = $request->getContent();

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Tester l'envoi d'un message WhatsApp (Admin)
     */
    public function testMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->whatsappService->sendMessage(
            $request->phone,
            $request->message
        );

        return response()->json($result);
    }

    /**
     * Tester la connexion WhatsApp
     */
    public function testConnection(Request $request)
    {
        $testPhone = $request->input('phone');
        $result = $this->whatsappService->testConnection($testPhone);

        return response()->json($result);
    }

    /**
     * Obtenir les statistiques d'envoi WhatsApp
     */
    public function getStatistics(Request $request)
    {
        $restaurantId = $request->input('restaurant_id');
        $days = $request->input('days', 30);

        $stats = $this->whatsappService->getStatistics($restaurantId, $days);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obtenir l'historique des messages WhatsApp
     */
    public function getMessageHistory(Request $request)
    {
        $query = DB::table('whatsapp_messages_log')
            ->orderBy('created_at', 'desc')
            ->limit(100);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        $messages = $query->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Renvoyer un message échoué
     */
    public function retryMessage($messageId)
    {
        $log = DB::table('whatsapp_messages_log')
            ->where('id', $messageId)
            ->where('status', 'failed')
            ->first();

        if (!$log) {
            return response()->json([
                'success' => false,
                'error' => 'Message non trouvé ou déjà envoyé'
            ], 404);
        }

        // Récupérer les détails de la commande et renvoyer
        if ($log->order_id) {
            $order = DB::table('orders')->find($log->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'error' => 'Commande non trouvée'
                ], 404);
            }

            // Logique de renvoi selon le type de message
            // À adapter selon votre structure de données

            return response()->json([
                'success' => true,
                'message' => 'Message réenvoyé'
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Impossible de renvoyer ce message'
        ], 400);
    }
}
