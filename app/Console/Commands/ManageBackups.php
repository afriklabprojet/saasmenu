<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class ManageBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:manage
                            {action : Action à effectuer (list, restore, clean)}
                            {--backup= : Nom du backup pour restauration}
                            {--force : Force l\'action sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gérer les backups RestroSaaS (lister, restaurer, nettoyer)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $backupService = new BackupService();

        switch ($action) {
            case 'list':
                $this->listBackups($backupService);
                break;

            case 'restore':
                $this->restoreBackup($backupService);
                break;

            case 'clean':
                $this->cleanBackups($backupService);
                break;

            default:
                $this->error("Action inconnue: {$action}");
                $this->info("Actions disponibles: list, restore, clean");
                return 1;
        }

        return 0;
    }

    /**
     * Lister les backups disponibles
     */
    private function listBackups(BackupService $backupService)
    {
        $this->info('📋 Backups Disponibles');
        $this->info('=====================');

        $backups = $backupService->listBackups();

        if (empty($backups)) {
            $this->warn('Aucun backup trouvé.');
            return;
        }

        $this->table(
            ['Nom', 'Taille', 'Date de création', 'Âge (jours)'],
            array_map(function($backup) {
                return [
                    $backup['name'],
                    $backup['size'],
                    $backup['created_at'],
                    $backup['age_days']
                ];
            }, $backups)
        );

        $this->newLine();
        $this->info("Total: " . count($backups) . " backup(s)");
    }

    /**
     * Restaurer un backup
     */
    private function restoreBackup(BackupService $backupService)
    {
        $backupName = $this->option('backup');

        if (!$backupName) {
            $backups = $backupService->listBackups();

            if (empty($backups)) {
                $this->error('Aucun backup disponible pour la restauration.');
                return;
            }

            $choices = array_map(function($backup) {
                return $backup['name'] . ' (' . $backup['size'] . ')';
            }, $backups);

            $selected = $this->choice('Sélectionner un backup à restaurer:', $choices);
            $backupName = explode(' (', $selected)[0];
        }

        $this->warn('⚠️  ATTENTION: La restauration va écraser les données actuelles !');

        if (!$this->option('force')) {
            if (!$this->confirm("Êtes-vous sûr de vouloir restaurer le backup '{$backupName}' ?")) {
                $this->info('Restauration annulée.');
                return;
            }
        }

        try {
            $this->info("🔄 Restauration du backup: {$backupName}");

            $result = $backupService->restoreBackup($backupName);

            $this->info('✅ ' . $result['message']);
            $this->warn('⚠️  Fichiers extraits dans: ' . $result['temp_dir']);
            $this->warn('⚠️  Restauration fichiers à faire manuellement pour sécurité');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la restauration: ' . $e->getMessage());
        }
    }

    /**
     * Nettoyer les anciens backups
     */
    private function cleanBackups(BackupService $backupService)
    {
        $this->info('🧹 Nettoyage des Anciens Backups');
        $this->info('===============================');

        $backups = $backupService->listBackups();
        $oldBackups = array_filter($backups, function($backup) {
            return $backup['age_days'] > 30;
        });

        if (empty($oldBackups)) {
            $this->info('✅ Aucun backup ancien à nettoyer.');
            return;
        }

        $this->table(
            ['Nom', 'Âge (jours)', 'Taille'],
            array_map(function($backup) {
                return [
                    $backup['name'],
                    $backup['age_days'],
                    $backup['size']
                ];
            }, $oldBackups)
        );

        if (!$this->option('force')) {
            if (!$this->confirm('Supprimer ces ' . count($oldBackups) . ' backup(s) ancien(s) ?')) {
                $this->info('Nettoyage annulé.');
                return;
            }
        }

        // Le nettoyage est géré automatiquement par le service
        // On force juste une exécution
        $backupService->createFullBackup(); // Déclenche le nettoyage automatique

        $this->info('✅ Nettoyage terminé.');
    }
}
