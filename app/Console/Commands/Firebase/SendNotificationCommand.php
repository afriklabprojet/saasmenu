<?php

namespace App\Console\Commands\Firebase;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:send-notification {notification_id? : ID de la notification à envoyer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des notifications Firebase';

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationId = $this->argument('notification_id');

        try {
            if ($notificationId) {
                // Envoyer une notification spécifique
                $notification = Notification::findOrFail($notificationId);
                $this->info("Envoi de la notification #{$notificationId}...");

                $result = $this->firebaseService->sendNotification($notification);

                if ($result['success']) {
                    $this->info("Notification envoyée avec succès à {$result['sent_count']} destinataire(s).");
                } else {
                    $this->error("Erreur lors de l'envoi: " . $result['message']);
                }
            } else {
                // Envoyer toutes les notifications en attente
                $this->info("Recherche des notifications en attente...");

                $pendingNotifications = Notification::where('status', 'pending')
                    ->where('scheduled_at', '<=', now())
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($pendingNotifications->isEmpty()) {
                    $this->info("Aucune notification en attente.");
                    return;
                }

                $this->info("Trouvé {$pendingNotifications->count()} notification(s) en attente.");

                $totalSent = 0;
                foreach ($pendingNotifications as $notification) {
                    $this->info("Envoi de la notification #{$notification->id}...");

                    $result = $this->firebaseService->sendNotification($notification);

                    if ($result['success']) {
                        $sentCount = $result['sent_count'] ?? 0;
                        $totalSent += $sentCount;
                        $this->line("✓ Notification #{$notification->id} envoyée à {$sentCount} destinataire(s).");
                    } else {
                        $this->error("✗ Notification #{$notification->id} échouée: " . $result['message']);
                    }
                }

                $this->info("Envoi terminé. Total: {$totalSent} notification(s) envoyée(s).");
            }

        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi: " . $e->getMessage());
            Log::error('Erreur SendNotificationCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
