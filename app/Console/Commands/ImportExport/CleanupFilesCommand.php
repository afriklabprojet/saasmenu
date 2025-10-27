<?php

namespace App\Console\Commands\ImportExport;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-export:cleanup {--days=7 : Nombre de jours après lesquels supprimer les fichiers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les anciens fichiers d\'import/export';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        try {
            $this->info("Nettoyage des fichiers d'import/export plus anciens que {$days} jours...");

            // Nettoyer les fichiers d'import
            $importPath = 'imports';
            $importFiles = Storage::disk('local')->files($importPath);
            $deletedImportFiles = 0;

            foreach ($importFiles as $file) {
                $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));

                if ($lastModified->lt($cutoffDate)) {
                    Storage::disk('local')->delete($file);
                    $deletedImportFiles++;
                    $this->line("Supprimé: {$file}");
                }
            }

            // Nettoyer les fichiers d'export
            $exportPath = 'exports';
            $exportFiles = Storage::disk('local')->files($exportPath);
            $deletedExportFiles = 0;

            foreach ($exportFiles as $file) {
                $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));

                if ($lastModified->lt($cutoffDate)) {
                    Storage::disk('local')->delete($file);
                    $deletedExportFiles++;
                    $this->line("Supprimé: {$file}");
                }
            }

            // Nettoyer les fichiers temporaires
            $tempPath = 'temp';
            $tempFiles = Storage::disk('local')->files($tempPath);
            $deletedTempFiles = 0;

            foreach ($tempFiles as $file) {
                $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));

                if ($lastModified->lt($cutoffDate)) {
                    Storage::disk('local')->delete($file);
                    $deletedTempFiles++;
                    $this->line("Supprimé: {$file}");
                }
            }

            $totalDeleted = $deletedImportFiles + $deletedExportFiles + $deletedTempFiles;

            $this->info("Nettoyage terminé.");
            $this->info("Fichiers supprimés:");
            $this->line("- Imports: {$deletedImportFiles}");
            $this->line("- Exports: {$deletedExportFiles}");
            $this->line("- Temporaires: {$deletedTempFiles}");
            $this->line("- Total: {$totalDeleted}");

        } catch (\Exception $e) {
            $this->error("Erreur lors du nettoyage: " . $e->getMessage());
            Log::error('Erreur CleanupFilesCommand: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
