<?php

namespace App\Console\Commands\Firebase;

use Illuminate\Console\Command;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class ProcessScheduledNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter les notifications Firebase programmées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info("Vérification des notifications programmées...");

            // Récupérer les notifications programmées dont l'heure d'envoi est passée
            $scheduledNotifications = Notification::where('status', 'scheduled')
                ->where('scheduled_at', '<=', now())
                ->orderBy('scheduled_at', 'asc')
                ->get();

            if ($scheduledNotifications->isEmpty()) {
                $this->info("Aucune notification programmée à traiter.");
                return;
            }

            $this->info("Trouvé {$scheduledNotifications->count()} notification(s) programmée(s).");

            foreach ($scheduledNotifications as $notification) {
                $this->info("Traitement de la notification #{$notification->id}...");

                // Marquer comme en cours de traitement
                $notification->update(['status' => 'pending']);

                // Ajouter à la queue pour envoi asynchrone
                Queue::push('firebase:send-notification', ['notification_id' => $notification->id]);

                $this->line("✓ Notification #{$notification->id} ajoutée à la queue.");
            }

            // Traiter les notifications récurrentes
            $this->info("Vérification des notifications récurrentes...");

            $recurringNotifications = Notification::where('is_recurring', true)
                ->where('status', 'active')
                ->where('next_send_at', '<=', now())
                ->get();

            if ($recurringNotifications->isNotEmpty()) {
                $this->info("Trouvé {$recurringNotifications->count()} notification(s) récurrente(s).");

                foreach ($recurringNotifications as $notification) {
                    $this->info("Traitement de la notification récurrente #{$notification->id}...");

                    // Créer une nouvelle instance pour envoi
                    $newNotification = $notification->replicate();
                    $newNotification->status = 'pending';
                    $newNotification->scheduled_at = now();
                    $newNotification->is_recurring = false;
                    $newNotification->save();

                    // Calculer la prochaine occurrence
                    $nextSendAt = $this->calculateNextOccurrence($notification);
                    $notification->update(['next_send_at' => $nextSendAt]);

                    // Ajouter la nouvelle instance à la queue
                    Queue::push('firebase:send-notification', ['notification_id' => $newNotification->id]);

                    $this->line("✓ Notification récurrente #{$notification->id} programmée pour {$nextSendAt}.");
                }
            } else {
                $this->info("Aucune notification récurrente à traiter.");
            }

            $this->info("Traitement des notifications programmées terminé.");

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: " . $e->getMessage());
            Log::error('Erreur ProcessScheduledNotificationsCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Calculer la prochaine occurrence d'une notification récurrente
     */
    private function calculateNextOccurrence($notification)
    {
        $currentTime = Carbon::parse($notification->next_send_at);

        switch ($notification->recurrence_type) {
            case 'daily':
                return $currentTime->addDay();
            case 'weekly':
                return $currentTime->addWeek();
            case 'monthly':
                return $currentTime->addMonth();
            case 'yearly':
                return $currentTime->addYear();
            default:
                return $currentTime->addDay();
        }
    }
}
