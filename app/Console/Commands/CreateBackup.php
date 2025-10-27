<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create
                            {--type=full : Type de backup (full, database, files)}
                            {--name= : Nom personnalisé pour le backup}
                            {--compress : Compresser le backup}
                            {--verify : Vérifier l\'intégrité après création}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un backup complet du système RestroSaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Création Backup RestroSaaS');
        $this->info('============================');

        $backupService = new BackupService();

        try {
            $this->info('⏳ Démarrage du backup...');

            $startTime = microtime(true);
            $backupInfo = $backupService->createFullBackup();
            $endTime = microtime(true);

            $executionTime = round(($endTime - $startTime), 2);

            $this->newLine();
            $this->info('✅ Backup créé avec succès !');
            $this->newLine();

            // Afficher informations du backup
            $this->table(
                ['Propriété', 'Valeur'],
                [
                    ['Nom du backup', $backupInfo['name']],
                    ['Taille', $backupInfo['size']],
                    ['Date de création', $backupInfo['created_at']],
                    ['Temps d\'exécution', $executionTime . ' secondes'],
                    ['Intégrité', $backupInfo['integrity_check']['status']],
                ]
            );

            // Afficher composants
            $this->comment('📦 Composants inclus:');
            foreach ($backupInfo['components'] as $component => $included) {
                $status = $included ? '✅' : '❌';
                $this->line("  {$status} " . ucfirst($component));
            }

            if ($this->option('verify')) {
                $this->newLine();
                $this->info('🔍 Vérification de l\'intégrité...');
                $integrity = $backupInfo['integrity_check'];

                if ($integrity['status'] === 'success') {
                    $this->info('✅ ' . $integrity['message']);
                } else {
                    $this->warn('⚠️  ' . $integrity['message']);
                }
            }

            $this->newLine();
            $this->info("📁 Backup sauvegardé: {$backupInfo['path']}");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création du backup:');
            $this->error($e->getMessage());

            Log::error('Erreur backup command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
