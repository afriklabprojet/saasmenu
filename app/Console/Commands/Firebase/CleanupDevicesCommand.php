<?php

namespace App\Console\Commands\Firebase;

use Illuminate\Console\Command;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupDevicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:cleanup-devices {--days=30 : Nombre de jours après lesquels supprimer les tokens inactifs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les tokens d\'appareils inactifs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        try {
            $this->info("Nettoyage des tokens d'appareils inactifs depuis plus de {$days} jours...");

            // Supprimer les tokens expirés
            $expiredTokens = DeviceToken::where('last_used_at', '<', $cutoffDate)
                ->orWhere('is_active', false)
                ->get();

            if ($expiredTokens->isEmpty()) {
                $this->info("Aucun token expiré trouvé.");
                return;
            }

            $this->info("Trouvé {$expiredTokens->count()} token(s) expiré(s).");

            $deletedCount = 0;
            foreach ($expiredTokens as $token) {
                $this->line("Suppression du token pour l'utilisateur #{$token->user_id}...");
                $token->delete();
                $deletedCount++;
            }

            // Nettoyer les doublons
            $this->info("Nettoyage des tokens en double...");

            $duplicates = DeviceToken::select('user_id', 'device_token')
                ->groupBy('user_id', 'device_token')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $duplicatesDeleted = 0;
            foreach ($duplicates as $duplicate) {
                $tokens = DeviceToken::where('user_id', $duplicate->user_id)
                    ->where('device_token', $duplicate->device_token)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Garder le plus récent, supprimer les autres
                for ($i = 1; $i < $tokens->count(); $i++) {
                    $tokens[$i]->delete();
                    $duplicatesDeleted++;
                }
            }

            $this->info("Nettoyage terminé.");
            $this->info("Tokens supprimés:");
            $this->line("- Expirés: {$deletedCount}");
            $this->line("- Doublons: {$duplicatesDeleted}");
            $this->line("- Total: " . ($deletedCount + $duplicatesDeleted));

        } catch (\Exception $e) {
            $this->error("Erreur lors du nettoyage: " . $e->getMessage());
            Log::error('Erreur CleanupDevicesCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
