<?php

namespace App\Console\Commands\ImportExport;

use Illuminate\Console\Command;
use App\Services\ImportExportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ProcessScheduledJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-export:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter les tâches d\'import/export programmées';

    protected $importExportService;

    public function __construct(ImportExportService $importExportService)
    {
        parent::__construct();
        $this->importExportService = $importExportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info("Vérification des tâches programmées...");

            // Traiter les imports programmés
            $scheduledImports = $this->importExportService->getScheduledImports();

            if ($scheduledImports->isNotEmpty()) {
                $this->info("Trouvé {$scheduledImports->count()} import(s) programmé(s).");

                foreach ($scheduledImports as $import) {
                    $this->info("Démarrage de l'import programmé #{$import->id}...");

                    // Ajouter à la queue pour traitement asynchrone
                    Queue::push('import-export:process-import', ['job_id' => $import->id]);

                    $this->line("✓ Import #{$import->id} ajouté à la queue.");
                }
            } else {
                $this->info("Aucun import programmé à traiter.");
            }

            // Traiter les exports programmés
            $scheduledExports = $this->importExportService->getScheduledExports();

            if ($scheduledExports->isNotEmpty()) {
                $this->info("Trouvé {$scheduledExports->count()} export(s) programmé(s).");

                foreach ($scheduledExports as $export) {
                    $this->info("Démarrage de l'export programmé #{$export->id}...");

                    // Ajouter à la queue pour traitement asynchrone
                    Queue::push('import-export:process-export', ['job_id' => $export->id]);

                    $this->line("✓ Export #{$export->id} ajouté à la queue.");
                }
            } else {
                $this->info("Aucun export programmé à traiter.");
            }

            // Traiter les tâches récurrentes
            $recurringTasks = $this->importExportService->getRecurringTasks();

            if ($recurringTasks->isNotEmpty()) {
                $this->info("Trouvé {$recurringTasks->count()} tâche(s) récurrente(s).");

                foreach ($recurringTasks as $task) {
                    $this->info("Traitement de la tâche récurrente #{$task->id}...");

                    $result = $this->importExportService->processRecurringTask($task);

                    if ($result['success']) {
                        $this->line("✓ Tâche #{$task->id} traitée avec succès.");
                    } else {
                        $this->error("✗ Erreur avec la tâche #{$task->id}: " . $result['message']);
                    }
                }
            } else {
                $this->info("Aucune tâche récurrente à traiter.");
            }

            $this->info("Traitement des tâches programmées terminé.");

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: " . $e->getMessage());
            Log::error('Erreur ProcessScheduledJobsCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
