<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    private $backupPath;
    private $retention_days = 30; // Conserver 30 jours de backups
    private $maxBackupsCount = 50; // Maximum 50 backups

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->ensureBackupDirectory();
    }

    /**
     * Backup complet du système
     */
    public function createFullBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "restro-saas-backup-{$timestamp}";

        Log::info("Démarrage backup complet: {$backupName}");

        try {
            // 1. Backup base de données
            $dbBackupPath = $this->backupDatabase($backupName);

            // 2. Backup fichiers application
            $filesBackupPath = $this->backupFiles($backupName);

            // 3. Backup uploads et assets
            $uploadsBackupPath = $this->backupUploads($backupName);

            // 4. Créer archive complète
            $fullBackupPath = $this->createFullArchive($backupName, [
                'database' => $dbBackupPath,
                'files' => $filesBackupPath,
                'uploads' => $uploadsBackupPath
            ]);

            // 5. Nettoyer anciens backups
            $this->cleanOldBackups();

            // 6. Vérifier intégrité
            $integrity = $this->verifyBackupIntegrity($fullBackupPath);

            $backupInfo = [
                'name' => $backupName,
                'path' => $fullBackupPath,
                'size' => $this->formatBytes(filesize($fullBackupPath)),
                'created_at' => now()->toISOString(),
                'integrity_check' => $integrity,
                'components' => [
                    'database' => file_exists($dbBackupPath),
                    'files' => file_exists($filesBackupPath),
                    'uploads' => file_exists($uploadsBackupPath)
                ]
            ];

            Log::info("Backup terminé avec succès", $backupInfo);

            return $backupInfo;

        } catch (\Exception $e) {
            Log::error("Erreur lors du backup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Backup de la base de données
     */
    private function backupDatabase($backupName)
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        $filename = "{$backupName}_database.sql";
        $filePath = $this->backupPath . '/' . $filename;

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port'] ?? 3306),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($filePath)
        );

        $result = null;
        $output = [];
        exec($command . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new \Exception("Erreur backup base de données: " . implode("\n", $output));
        }

        // Vérifier que le fichier a été créé et n'est pas vide
        if (!file_exists($filePath) || filesize($filePath) < 1024) {
            throw new \Exception("Backup base de données invalide ou vide");
        }

        Log::info("Backup base de données créé: {$filename}", [
            'size' => $this->formatBytes(filesize($filePath))
        ]);

        return $filePath;
    }

    /**
     * Backup des fichiers application critiques
     */
    private function backupFiles($backupName)
    {
        $filename = "{$backupName}_files.tar.gz";
        $filePath = $this->backupPath . '/' . $filename;

        $criticalPaths = [
            '.env',
            'app/',
            'config/',
            'database/migrations/',
            'database/seeders/',
            'routes/',
            'resources/views/',
            'public/assets/',
            'composer.json',
            'composer.lock',
            'package.json'
        ];

        $baseDir = base_path();
        $tarCommand = "cd " . escapeshellarg($baseDir) . " && tar -czf " . escapeshellarg($filePath);

        foreach ($criticalPaths as $path) {
            if (file_exists($baseDir . '/' . $path)) {
                $tarCommand .= " " . escapeshellarg($path);
            }
        }

        $result = null;
        $output = [];
        exec($tarCommand . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new \Exception("Erreur backup fichiers: " . implode("\n", $output));
        }

        Log::info("Backup fichiers créé: {$filename}", [
            'size' => $this->formatBytes(filesize($filePath))
        ]);

        return $filePath;
    }

    /**
     * Backup des uploads et assets utilisateurs
     */
    private function backupUploads($backupName)
    {
        $filename = "{$backupName}_uploads.tar.gz";
        $filePath = $this->backupPath . '/' . $filename;

        $uploadPaths = [
            'storage/app/public/',
            'public/uploads/',
            'public/storage/',
        ];

        $baseDir = base_path();
        $tarCommand = "cd " . escapeshellarg($baseDir) . " && tar -czf " . escapeshellarg($filePath);

        foreach ($uploadPaths as $path) {
            if (is_dir($baseDir . '/' . $path)) {
                $tarCommand .= " " . escapeshellarg($path);
            }
        }

        $result = null;
        $output = [];
        exec($tarCommand . ' 2>&1', $output, $result);

        if ($result !== 0) {
            Log::warning("Backup uploads partiel: " . implode("\n", $output));
            // Créer archive vide si pas d'uploads
            file_put_contents($filePath, '');
        }

        Log::info("Backup uploads créé: {$filename}", [
            'size' => $this->formatBytes(filesize($filePath))
        ]);

        return $filePath;
    }

    /**
     * Créer archive complète avec tous les composants
     */
    private function createFullArchive($backupName, $components)
    {
        $zipFilename = "{$backupName}_complete.zip";
        $zipPath = $this->backupPath . '/' . $zipFilename;

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception("Impossible de créer l'archive ZIP: {$zipPath}");
        }

        // Ajouter chaque composant à l'archive
        foreach ($components as $type => $filePath) {
            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            }
        }

        // Ajouter fichier manifest avec informations backup
        $manifest = [
            'backup_name' => $backupName,
            'created_at' => now()->toISOString(),
            'restro_saas_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'components' => array_keys($components),
            'total_size' => array_sum(array_map('filesize', array_filter($components, 'file_exists')))
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        // Supprimer fichiers temporaires
        foreach ($components as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        Log::info("Archive complète créée: {$zipFilename}", [
            'size' => $this->formatBytes(filesize($zipPath))
        ]);

        return $zipPath;
    }

    /**
     * Vérifier l'intégrité du backup
     */
    private function verifyBackupIntegrity($backupPath)
    {
        if (!file_exists($backupPath)) {
            return ['status' => 'error', 'message' => 'Fichier backup introuvable'];
        }

        // Vérifier que l'archive n'est pas corrompue
        $zip = new ZipArchive();
        $result = $zip->open($backupPath, ZipArchive::CHECKCONS);

        if ($result !== TRUE) {
            return ['status' => 'error', 'message' => 'Archive corrompue'];
        }

        // Vérifier présence des fichiers essentiels
        $requiredFiles = ['_database.sql', '_files.tar.gz', 'manifest.json'];
        $foundFiles = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $foundFiles[] = $zip->getNameIndex($i);
        }

        $zip->close();

        $missingFiles = [];
        foreach ($requiredFiles as $required) {
            $found = false;
            foreach ($foundFiles as $file) {
                if (strpos($file, $required) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingFiles[] = $required;
            }
        }

        if (empty($missingFiles)) {
            return ['status' => 'success', 'message' => 'Intégrité vérifiée'];
        } else {
            return ['status' => 'warning', 'message' => 'Fichiers manquants: ' . implode(', ', $missingFiles)];
        }
    }

    /**
     * Nettoyer les anciens backups
     */
    private function cleanOldBackups()
    {
        $files = glob($this->backupPath . '/*_complete.zip');

        // Trier par date de modification (plus ancien en premier)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        $deleted = 0;

        // Supprimer par date (plus de X jours)
        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$this->retention_days} days")) {
                unlink($file);
                $deleted++;
                Log::info("Backup expiré supprimé: " . basename($file));
            }
        }

        // Supprimer par nombre (garder les X plus récents)
        $remainingFiles = array_filter($files, 'file_exists');
        if (count($remainingFiles) > $this->maxBackupsCount) {
            $toDelete = array_slice($remainingFiles, 0, count($remainingFiles) - $this->maxBackupsCount);
            foreach ($toDelete as $file) {
                unlink($file);
                $deleted++;
                Log::info("Backup surnuméraire supprimé: " . basename($file));
            }
        }

        if ($deleted > 0) {
            Log::info("Nettoyage backups: {$deleted} fichier(s) supprimé(s)");
        }
    }

    /**
     * Lister les backups disponibles
     */
    public function listBackups()
    {
        $files = glob($this->backupPath . '/*_complete.zip');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file, '_complete.zip'),
                'file' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'created_at' => Carbon::createFromTimestamp(filemtime($file))->toISOString(),
                'age_days' => Carbon::createFromTimestamp(filemtime($file))->diffInDays(now())
            ];
        }

        // Trier par date (plus récent en premier)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Restaurer un backup
     */
    public function restoreBackup($backupName)
    {
        $backupFile = $this->backupPath . '/' . $backupName . '_complete.zip';

        if (!file_exists($backupFile)) {
            throw new \Exception("Backup non trouvé: {$backupName}");
        }

        Log::warning("ATTENTION: Début de restauration backup {$backupName}");

        // Créer répertoire temporaire pour extraction
        $tempDir = $this->backupPath . '/restore_' . time();
        mkdir($tempDir);

        try {
            // Extraire l'archive
            $zip = new ZipArchive();
            if ($zip->open($backupFile) !== TRUE) {
                throw new \Exception("Impossible d'ouvrir l'archive backup");
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Restaurer base de données
            $this->restoreDatabase($tempDir);

            // Restaurer fichiers (à faire manuellement pour sécurité)
            Log::warning("ATTENTION: Restauration fichiers doit être faite manuellement depuis: {$tempDir}");

            Log::info("Restauration backup terminée: {$backupName}");

            return [
                'status' => 'success',
                'message' => 'Backup restauré avec succès',
                'temp_dir' => $tempDir
            ];

        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
            throw $e;
        }
    }

    /**
     * Restaurer la base de données depuis un backup
     */
    private function restoreDatabase($tempDir)
    {
        $sqlFiles = glob($tempDir . '/*_database.sql');

        if (empty($sqlFiles)) {
            throw new \Exception("Fichier SQL non trouvé dans le backup");
        }

        $sqlFile = $sqlFiles[0];
        $dbConfig = config('database.connections.' . config('database.default'));

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['port'] ?? 3306),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($dbConfig['database']),
            escapeshellarg($sqlFile)
        );

        $result = null;
        $output = [];
        exec($command . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new \Exception("Erreur restauration base de données: " . implode("\n", $output));
        }

        Log::info("Base de données restaurée depuis: " . basename($sqlFile));
    }

    /**
     * Utilitaires
     */
    private function ensureBackupDirectory()
    {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
