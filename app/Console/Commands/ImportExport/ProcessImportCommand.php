<?php

namespace App\Console\Commands\ImportExport;

use Illuminate\Console\Command;
use App\Services\ImportExportService;
use App\Models\ImportJob;
use Illuminate\Support\Facades\Log;

class ProcessImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-export:process-import {job_id? : ID du job d\'import à traiter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter les imports en attente';

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
                $job = ImportJob::findOrFail($jobId);
                $this->info("Traitement du job d'import #{$jobId}...");

                $result = $this->importExportService->processImportJob($job);

                if ($result['success']) {
                    $this->info("Import terminé avec succès. {$result['processed']} éléments traités.");
                } else {
                    $this->error("Erreur lors de l'import: " . $result['message']);
                }
            } else {
                // Traiter tous les jobs en attente
                $this->info("Recherche des imports en attente...");

                $pendingJobs = ImportJob::where('status', 'pending')
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($pendingJobs->isEmpty()) {
                    $this->info("Aucun import en attente.");
                    return;
                }

                $this->info("Trouvé {$pendingJobs->count()} import(s) en attente.");

                foreach ($pendingJobs as $job) {
                    $this->info("Traitement du job #{$job->id}...");

                    $result = $this->importExportService->processImportJob($job);

                    if ($result['success']) {
                        $this->info("✓ Job #{$job->id} terminé. {$result['processed']} éléments traités.");
                    } else {
                        $this->error("✗ Job #{$job->id} échoué: " . $result['message']);
                    }
                }
            }

            $this->info("Traitement terminé.");

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: " . $e->getMessage());
            Log::error('Erreur ProcessImportCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
