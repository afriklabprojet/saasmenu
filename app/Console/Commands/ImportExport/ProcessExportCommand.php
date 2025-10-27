<?php

namespace App\Console\Commands\ImportExport;

use Illuminate\Console\Command;
use App\Services\ImportExportService;
use App\Models\ExportJob;
use Illuminate\Support\Facades\Log;

class ProcessExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-export:process-export {job_id? : ID du job d\'export à traiter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter les exports en attente';

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
        $jobId = $this->argument('job_id');

        try {
            if ($jobId) {
                // Traiter un job spécifique
                $job = ExportJob::findOrFail($jobId);
                $this->info("Traitement du job d'export #{$jobId}...");

                $result = $this->importExportService->processExportJob($job);

                if ($result['success']) {
                    $this->info("Export terminé avec succès. Fichier: {$result['file_path']}");
                } else {
                    $this->error("Erreur lors de l'export: " . $result['message']);
                }
            } else {
                // Traiter tous les jobs en attente
                $this->info("Recherche des exports en attente...");

                $pendingJobs = ExportJob::where('status', 'pending')
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($pendingJobs->isEmpty()) {
                    $this->info("Aucun export en attente.");
                    return;
                }

                $this->info("Trouvé {$pendingJobs->count()} export(s) en attente.");

                foreach ($pendingJobs as $job) {
                    $this->info("Traitement du job #{$job->id}...");

                    $result = $this->importExportService->processExportJob($job);

                    if ($result['success']) {
                        $this->info("✓ Job #{$job->id} terminé. Fichier: {$result['file_path']}");
                    } else {
                        $this->error("✗ Job #{$job->id} échoué: " . $result['message']);
                    }
                }
            }

            $this->info("Traitement terminé.");

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: " . $e->getMessage());
            Log::error('Erreur ProcessExportCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
