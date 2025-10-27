<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    private $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    /**
     * Dashboard de gestion des backups
     */
    public function index()
    {
        $backups = $this->backupService->listBackups();

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Créer un nouveau backup
     */
    public function create(Request $request)
    {
        try {
            Log::info('Démarrage backup depuis interface web', [
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            $backupInfo = $this->backupService->createFullBackup();

            return response()->json([
                'success' => true,
                'message' => 'Backup créé avec succès',
                'backup' => $backupInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création backup web', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les backups via API
     */
    public function list()
    {
        try {
            $backups = $this->backupService->listBackups();

            return response()->json([
                'success' => true,
                'backups' => $backups,
                'count' => count($backups)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharger un backup
     */
    public function download($backupName)
    {
        try {
            $backupPath = storage_path("app/backups/{$backupName}_complete.zip");

            if (!file_exists($backupPath)) {
                abort(404, 'Backup non trouvé');
            }

            Log::info('Téléchargement backup', [
                'backup' => $backupName,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);

            return Response::download($backupPath, basename($backupPath), [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . basename($backupPath) . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement backup', [
                'backup' => $backupName,
                'error' => $e->getMessage()
            ]);

            abort(500, 'Erreur lors du téléchargement');
        }
    }

    /**
     * Supprimer un backup
     */
    public function delete($backupName)
    {
        try {
            $backupPath = storage_path("app/backups/{$backupName}_complete.zip");

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup non trouvé'
                ], 404);
            }

            unlink($backupPath);

            Log::warning('Backup supprimé', [
                'backup' => $backupName,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression backup', [
                'backup' => $backupName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Informations détaillées d'un backup
     */
    public function info($backupName)
    {
        try {
            $backupPath = storage_path("app/backups/{$backupName}_complete.zip");

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup non trouvé'
                ], 404);
            }

            $info = [
                'name' => $backupName,
                'file' => basename($backupPath),
                'size' => $this->formatBytes(filesize($backupPath)),
                'created_at' => date('Y-m-d H:i:s', filemtime($backupPath)),
                'age_days' => floor((time() - filemtime($backupPath)) / 86400)
            ];

            // Vérifier intégrité
            $integrity = $this->verifyBackupIntegrity($backupPath);
            $info['integrity'] = $integrity;

            return response()->json([
                'success' => true,
                'backup' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurer un backup (interface web)
     */
    public function restore(Request $request, $backupName)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        try {
            Log::critical('DÉBUT RESTAURATION BACKUP', [
                'backup' => $backupName,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            $result = $this->backupService->restoreBackup($backupName);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'temp_dir' => $result['temp_dir']
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur restauration backup web', [
                'backup' => $backupName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statut espace de stockage backups
     */
    public function storageStatus()
    {
        try {
            $backupDir = storage_path('app/backups');
            $totalSize = 0;
            $fileCount = 0;

            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*_complete.zip');
                $fileCount = count($files);

                foreach ($files as $file) {
                    $totalSize += filesize($file);
                }
            }

            $freeSpace = disk_free_space($backupDir);

            return response()->json([
                'success' => true,
                'storage' => [
                    'backup_count' => $fileCount,
                    'total_size' => $this->formatBytes($totalSize),
                    'free_space' => $this->formatBytes($freeSpace),
                    'backup_dir' => $backupDir
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Utilitaires privés
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    private function verifyBackupIntegrity($backupPath)
    {
        try {
            $zip = new \ZipArchive();
            $result = $zip->open($backupPath, \ZipArchive::CHECKCONS);

            if ($result !== TRUE) {
                return ['status' => 'error', 'message' => 'Archive corrompue'];
            }

            $zip->close();
            return ['status' => 'success', 'message' => 'Intégrité vérifiée'];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
