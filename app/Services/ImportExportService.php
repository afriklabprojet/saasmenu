<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use App\Models\ImportExportJob;
use App\Models\ImportExportMapping;
use App\Models\ImportExportTemplate;
use Exception;

class ImportExportService
{
    protected $supportedFormats = ['csv', 'xlsx', 'xls', 'json'];
    protected $supportedTypes = ['menus', 'products', 'customers', 'orders', 'categories', 'restaurants', 'coupons', 'inventory'];

    /**
     * Analyser un fichier uploadé
     */
    public function analyzeFile($filePath, $type, $options = [])
    {
        try {
            $uploadId = Str::uuid()->toString();
            $fileInfo = $this->getFileInfo($filePath);

            // Lire un échantillon du fichier
            $preview = $this->generatePreview($filePath, $fileInfo['extension']);

            // Validation de base
            $validation = $this->performBasicValidation($preview, $type);

            // Stocker les informations d'analyse
            $this->storeAnalysis($uploadId, $filePath, $type, $fileInfo, $preview, $validation, $options);

            return [
                'upload_id' => $uploadId,
                'file_info' => $fileInfo,
                'preview' => $preview,
                'validation' => $validation
            ];

        } catch (Exception $e) {
            Log::error('File Analysis Error: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'analyse du fichier: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les informations d'un fichier
     */
    protected function getFileInfo($filePath)
    {
        $fullPath = storage_path('app/' . $filePath);
        $pathInfo = pathinfo($fullPath);

        return [
            'name' => $pathInfo['basename'],
            'extension' => strtolower($pathInfo['extension'] ?? ''),
            'size' => filesize($fullPath),
            'mime_type' => mime_content_type($fullPath),
            'path' => $filePath,
            'last_modified' => filemtime($fullPath),
        ];
    }

    /**
     * Générer un aperçu du fichier
     */
    protected function generatePreview($filePath, $extension)
    {
        $fullPath = storage_path('app/' . $filePath);

        switch ($extension) {
            case 'csv':
                return $this->previewCsv($fullPath);
            case 'xlsx':
            case 'xls':
                return $this->previewExcel($fullPath);
            case 'json':
                return $this->previewJson($fullPath);
            default:
                throw new Exception('Format de fichier non supporté: ' . $extension);
        }
    }

    /**
     * Aperçu d'un fichier CSV
     */
    protected function previewCsv($filePath, $limit = 5)
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            // Lire les en-têtes
            $headers = fgetcsv($handle);

            // Lire les premières lignes
            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== FALSE && $rowCount < $limit) {
                $data[] = array_combine($headers, $row);
                $rowCount++;
            }

            fclose($handle);
        }

        return [
            'headers' => $headers,
            'data' => $data,
            'total_rows' => $this->countCsvRows($filePath),
            'format' => 'csv'
        ];
    }

    /**
     * Aperçu d'un fichier Excel
     */
    protected function previewExcel($filePath, $limit = 5)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $headers = [];
            $data = [];

            // Obtenir les en-têtes (première ligne)
            $headerRow = $worksheet->getRowIterator(1, 1)->current();
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = $cell->getValue();
            }

            // Obtenir les données (lignes suivantes)
            $rowIterator = $worksheet->getRowIterator(2, $limit + 1);
            foreach ($rowIterator as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = array_combine($headers, array_pad($rowData, count($headers), ''));
            }

            return [
                'headers' => $headers,
                'data' => $data,
                'total_rows' => $worksheet->getHighestRow() - 1, // -1 pour exclure les en-têtes
                'format' => 'excel'
            ];

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
        }
    }

    /**
     * Aperçu d'un fichier JSON
     */
    protected function previewJson($filePath, $limit = 5)
    {
        $content = file_get_contents($filePath);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Fichier JSON invalide: ' . json_last_error_msg());
        }

        if (!is_array($json)) {
            throw new Exception('Le fichier JSON doit contenir un tableau d\'objets');
        }

        $headers = [];
        if (!empty($json)) {
            $headers = array_keys($json[0]);
        }

        return [
            'headers' => $headers,
            'data' => array_slice($json, 0, $limit),
            'total_rows' => count($json),
            'format' => 'json'
        ];
    }

    /**
     * Compter les lignes d'un fichier CSV
     */
    protected function countCsvRows($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, 'r');
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }
        fclose($handle);
        return $linecount - 1; // -1 pour exclure les en-têtes
    }

    /**
     * Validation de base
     */
    protected function performBasicValidation($preview, $type)
    {
        $errors = [];
        $warnings = [];
        $template = $this->getTemplate($type);

        // Vérifier les colonnes requises
        $missingColumns = array_diff($template['required_fields'], $preview['headers']);
        if (!empty($missingColumns)) {
            $errors[] = 'Colonnes manquantes: ' . implode(', ', $missingColumns);
        }

        // Vérifier les colonnes inconnues
        $unknownColumns = array_diff($preview['headers'], $template['all_fields']);
        if (!empty($unknownColumns)) {
            $warnings[] = 'Colonnes non reconnues: ' . implode(', ', $unknownColumns);
        }

        // Vérifier qu'il y a des données
        if (empty($preview['data'])) {
            $errors[] = 'Le fichier ne contient aucune donnée';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'suggestions' => $this->generateSuggestions($preview['headers'], $template)
        ];
    }

    /**
     * Générer des suggestions de mapping
     */
    protected function generateSuggestions($headers, $template)
    {
        $suggestions = [];

        foreach ($headers as $header) {
            $bestMatch = $this->findBestMatch($header, $template['all_fields']);
            if ($bestMatch) {
                $suggestions[$header] = $bestMatch;
            }
        }

        return $suggestions;
    }

    /**
     * Trouver la meilleure correspondance pour un en-tête
     */
    protected function findBestMatch($header, $fields)
    {
        $header = strtolower(trim($header));
        $bestScore = 0;
        $bestMatch = null;

        foreach ($fields as $field) {
            $fieldLower = strtolower($field);

            // Correspondance exacte
            if ($header === $fieldLower) {
                return $field;
            }

            // Correspondance partielle
            $score = similar_text($header, $fieldLower);
            if ($score > $bestScore && $score > strlen($header) * 0.6) {
                $bestScore = $score;
                $bestMatch = $field;
            }
        }

        return $bestMatch;
    }

    /**
     * Obtenir le template pour un type de données
     */
    public function getTemplate($type)
    {
        return match($type) {
            'menus' => [
                'required_fields' => ['name', 'description', 'price', 'category_id'],
                'all_fields' => ['id', 'name', 'description', 'price', 'category_id', 'image', 'is_active', 'is_available', 'ingredients', 'allergens', 'preparation_time', 'calories', 'restaurant_id'],
                'validation_rules' => [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'price' => 'required|numeric|min:0',
                    'category_id' => 'required|integer|exists:categories,id',
                ]
            ],
            'products' => [
                'required_fields' => ['name', 'price', 'category'],
                'all_fields' => ['id', 'name', 'description', 'price', 'category', 'sku', 'barcode', 'stock_quantity', 'min_stock', 'max_stock', 'is_active', 'weight', 'dimensions'],
                'validation_rules' => [
                    'name' => 'required|string|max:255',
                    'price' => 'required|numeric|min:0',
                    'category' => 'required|string|max:100',
                ]
            ],
            'customers' => [
                'required_fields' => ['name', 'email'],
                'all_fields' => ['id', 'name', 'email', 'phone', 'date_of_birth', 'address', 'city', 'postal_code', 'country', 'is_active', 'registration_date'],
                'validation_rules' => [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'nullable|string|max:20',
                ]
            ],
            'orders' => [
                'required_fields' => ['customer_email', 'total', 'status'],
                'all_fields' => ['id', 'customer_email', 'total', 'status', 'order_date', 'delivery_date', 'payment_method', 'delivery_address', 'notes', 'items'],
                'validation_rules' => [
                    'customer_email' => 'required|email',
                    'total' => 'required|numeric|min:0',
                    'status' => 'required|string|in:pending,confirmed,preparing,ready,delivered,cancelled',
                ]
            ],
            default => [
                'required_fields' => [],
                'all_fields' => [],
                'validation_rules' => []
            ]
        };
    }

    /**
     * Stocker l'analyse d'un fichier
     */
    protected function storeAnalysis($uploadId, $filePath, $type, $fileInfo, $preview, $validation, $options)
    {
        $analysisData = [
            'upload_id' => $uploadId,
            'file_path' => $filePath,
            'type' => $type,
            'file_info' => $fileInfo,
            'preview' => $preview,
            'validation' => $validation,
            'options' => $options,
            'created_at' => now(),
        ];

        // Stocker dans le cache ou une table temporaire
        cache()->put("import_analysis_{$uploadId}", $analysisData, now()->addHours(2));
    }

    /**
     * Valider un import
     */
    public function validateImport($uploadId, $mapping, $options = [])
    {
        try {
            $analysis = cache()->get("import_analysis_{$uploadId}");
            if (!$analysis) {
                throw new Exception('Analyse introuvable. Veuillez re-uploader le fichier.');
            }

            // Appliquer le mapping et valider les données
            $validationResult = $this->validateMappedData($analysis, $mapping, $options);

            return $validationResult;

        } catch (Exception $e) {
            Log::error('Import Validation Error: ' . $e->getMessage());
            throw new Exception('Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * Valider les données avec mapping
     */
    protected function validateMappedData($analysis, $mapping, $options)
    {
        $errors = [];
        $warnings = [];
        $stats = [
            'total_rows' => count($analysis['preview']['data']),
            'valid_rows' => 0,
            'invalid_rows' => 0,
        ];

        $template = $this->getTemplate($analysis['type']);

        foreach ($analysis['preview']['data'] as $index => $row) {
            $mappedRow = $this->applyMapping($row, $mapping);
            $rowErrors = $this->validateRow($mappedRow, $template['validation_rules']);

            if (empty($rowErrors)) {
                $stats['valid_rows']++;
            } else {
                $stats['invalid_rows']++;
                $errors[] = "Ligne " . ($index + 2) . ": " . implode(', ', $rowErrors);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => array_slice($errors, 0, 10), // Limiter à 10 erreurs pour l'affichage
            'warnings' => $warnings,
            'stats' => $stats,
            'has_more_errors' => count($errors) > 10,
        ];
    }

    /**
     * Appliquer le mapping à une ligne
     */
    protected function applyMapping($row, $mapping)
    {
        $mappedRow = [];

        foreach ($mapping as $sourceField => $targetField) {
            if ($targetField && isset($row[$sourceField])) {
                $mappedRow[$targetField] = $row[$sourceField];
            }
        }

        return $mappedRow;
    }

    /**
     * Valider une ligne de données
     */
    protected function validateRow($row, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $row[$field] ?? null;
            $fieldRules = explode('|', $rule);

            foreach ($fieldRules as $fieldRule) {
                if (strpos($fieldRule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $fieldRule, 2);
                } else {
                    $ruleName = $fieldRule;
                    $ruleValue = null;
                }

                if (!$this->validateFieldRule($value, $ruleName, $ruleValue)) {
                    $errors[] = "Champ '{$field}': {$this->getValidationMessage($ruleName, $ruleValue)}";
                    break; // Arrêter à la première erreur pour ce champ
                }
            }
        }

        return $errors;
    }

    /**
     * Valider une règle spécifique
     */
    protected function validateFieldRule($value, $rule, $parameter)
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'string':
                return is_string($value);
            case 'numeric':
                return is_numeric($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'min':
                return is_numeric($value) && $value >= $parameter;
            case 'max':
                return is_numeric($value) ? $value <= $parameter : strlen($value) <= $parameter;
            case 'in':
                $allowed = explode(',', $parameter);
                return in_array($value, $allowed);
            default:
                return true;
        }
    }

    /**
     * Obtenir le message d'erreur de validation
     */
    protected function getValidationMessage($rule, $parameter)
    {
        return match($rule) {
            'required' => 'Ce champ est obligatoire',
            'string' => 'Ce champ doit être une chaîne de caractères',
            'numeric' => 'Ce champ doit être numérique',
            'email' => 'Ce champ doit être une adresse email valide',
            'min' => "La valeur doit être supérieure ou égale à {$parameter}",
            'max' => "La valeur doit être inférieure ou égale à {$parameter}",
            'in' => "La valeur doit être l'une des suivantes: {$parameter}",
            default => 'Valeur invalide'
        };
    }

    /**
     * Traiter un import
     */
    public function processImport($uploadId, $mapping, $options = [])
    {
        try {
            $analysis = cache()->get("import_analysis_{$uploadId}");
            if (!$analysis) {
                throw new Exception('Analyse introuvable. Veuillez re-uploader le fichier.');
            }

            // Créer un job d'import
            $job = ImportExportJob::create([
                'type' => 'import',
                'data_type' => $analysis['type'],
                'file_path' => $analysis['file_path'],
                'status' => 'pending',
                'options' => array_merge($analysis['options'], $options),
                'mapping' => $mapping,
                'user_id' => auth()->id(),
                'total_records' => $analysis['preview']['total_rows'],
                'processed_records' => 0,
                'successful_records' => 0,
                'failed_records' => 0,
            ]);

            // Dispatcher le job en arrière-plan
            // ProcessImportJob::dispatch($job);

            return $job;

        } catch (Exception $e) {
            Log::error('Process Import Error: ' . $e->getMessage());
            throw new Exception('Erreur lors du traitement: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir le statut d'un job
     */
    public function getJobStatus($job)
    {
        return [
            'id' => $job->id,
            'status' => $job->status,
            'progress' => $job->total_records > 0 ? ($job->processed_records / $job->total_records) * 100 : 0,
            'total_records' => $job->total_records,
            'processed_records' => $job->processed_records,
            'successful_records' => $job->successful_records,
            'failed_records' => $job->failed_records,
            'errors' => $job->errors ?? [],
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at,
        ];
    }

    /**
     * Annuler un job
     */
    public function cancelJob($job)
    {
        if (in_array($job->status, ['pending', 'processing'])) {
            $job->update(['status' => 'cancelled']);
            return true;
        }
        return false;
    }

    /**
     * Télécharger un template
     */
    public function downloadTemplate($type)
    {
        try {
            $template = $this->getTemplate($type);
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Ajouter les en-têtes
            $column = 'A';
            foreach ($template['all_fields'] as $field) {
                $sheet->setCellValue($column . '1', $field);
                $column++;
            }

            // Styliser les en-têtes
            $sheet->getStyle('A1:' . chr(ord('A') + count($template['all_fields']) - 1) . '1')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E0E0E0']]
            ]);

            $writer = new Xlsx($spreadsheet);
            $filename = "template_{$type}_" . date('Y-m-d') . '.xlsx';
            $tempPath = storage_path('app/temp/' . $filename);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('Template Download Error: ' . $e->getMessage());
            throw new Exception('Erreur lors de la génération du template: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger un échantillon
     */
    public function downloadSample($type)
    {
        try {
            $template = $this->getTemplate($type);
            $sampleData = $this->generateSampleData($type, $template);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Ajouter les en-têtes
            $column = 'A';
            foreach ($template['all_fields'] as $field) {
                $sheet->setCellValue($column . '1', $field);
                $column++;
            }

            // Ajouter les données d'exemple
            $row = 2;
            foreach ($sampleData as $data) {
                $column = 'A';
                foreach ($template['all_fields'] as $field) {
                    $sheet->setCellValue($column . $row, $data[$field] ?? '');
                    $column++;
                }
                $row++;
            }

            // Styliser les en-têtes
            $sheet->getStyle('A1:' . chr(ord('A') + count($template['all_fields']) - 1) . '1')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E0E0E0']]
            ]);

            $writer = new Xlsx($spreadsheet);
            $filename = "sample_{$type}_" . date('Y-m-d') . '.xlsx';
            $tempPath = storage_path('app/temp/' . $filename);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('Sample Download Error: ' . $e->getMessage());
            throw new Exception('Erreur lors de la génération de l\'échantillon: ' . $e->getMessage());
        }
    }

    /**
     * Générer des données d'exemple
     */
    protected function generateSampleData($type, $template)
    {
        switch ($type) {
            case 'menus':
                return [
                    [
                        'name' => 'Pizza Margherita',
                        'description' => 'Pizza traditionnelle avec tomate, mozzarella et basilic',
                        'price' => '12.50',
                        'category_id' => '1',
                        'is_active' => '1',
                        'is_available' => '1',
                        'preparation_time' => '15',
                        'calories' => '250',
                    ],
                    [
                        'name' => 'Salade César',
                        'description' => 'Salade fraîche avec poulet grillé et parmesan',
                        'price' => '8.90',
                        'category_id' => '2',
                        'is_active' => '1',
                        'is_available' => '1',
                        'preparation_time' => '10',
                        'calories' => '180',
                    ]
                ];

            case 'customers':
                return [
                    [
                        'name' => 'Jean Dupont',
                        'email' => 'jean.dupont@example.com',
                        'phone' => '+33123456789',
                        'city' => 'Paris',
                        'postal_code' => '75001',
                        'country' => 'France',
                    ],
                    [
                        'name' => 'Marie Martin',
                        'email' => 'marie.martin@example.com',
                        'phone' => '+33987654321',
                        'city' => 'Lyon',
                        'postal_code' => '69000',
                        'country' => 'France',
                    ]
                ];

            default:
                return [];
        }
    }

    /**
     * Obtenir les exports disponibles
     */
    public function getAvailableExports()
    {
        return [
            'menus' => 'Menus et plats',
            'products' => 'Produits',
            'customers' => 'Clients',
            'orders' => 'Commandes',
            'categories' => 'Catégories',
            'restaurants' => 'Restaurants',
            'reports' => 'Rapports',
            'analytics' => 'Analyses',
        ];
    }

    /**
     * Générer un export
     */
    public function generateExport($type, $format, $filters = [], $options = [])
    {
        try {
            // Créer un job d'export
            $job = ImportExportJob::create([
                'type' => 'export',
                'data_type' => $type,
                'status' => 'pending',
                'format' => $format,
                'filters' => $filters,
                'options' => $options,
                'user_id' => auth()->id(),
            ]);

            // Dispatcher le job en arrière-plan
            // ProcessExportJob::dispatch($job);

            return $job;

        } catch (Exception $e) {
            Log::error('Generate Export Error: ' . $e->getMessage());
            throw new Exception('Erreur lors de la génération de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger un export
     */
    public function downloadExport($export)
    {
        try {
            if (!$export->file_path || !Storage::exists($export->file_path)) {
                throw new Exception('Fichier d\'export introuvable');
            }

            $filename = basename($export->file_path);
            return Storage::download($export->file_path, $filename);

        } catch (Exception $e) {
            Log::error('Export Download Error: ' . $e->getMessage());
            throw new Exception('Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les métriques du dashboard
     */
    public function getDashboardMetrics()
    {
        $totalJobs = ImportExportJob::count();
        $completedJobs = ImportExportJob::where('status', 'completed')->count();
        $failedJobs = ImportExportJob::where('status', 'failed')->count();
        $pendingJobs = ImportExportJob::where('status', 'pending')->count();

        return [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'failed_jobs' => $failedJobs,
            'pending_jobs' => $pendingJobs,
            'success_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 2) : 0,
            'recent_activity' => ImportExportJob::latest()->limit(5)->get(),
        ];
    }

    /**
     * Traiter un job d'import
     */
    public function processImportJob($job)
    {
        try {
            // Marquer le job comme en cours
            $job->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Lire le fichier
            $data = $this->readFileData($job->file_path, $job->settings['format'] ?? 'csv');

            $job->update(['total_rows' => count($data)]);

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                try {
                    // Traiter chaque ligne selon le type
                    $this->processImportRow($job->type, $row, $job->restaurant_id, $job->settings);
                    $successCount++;
                } catch (Exception $e) {
                    $failureCount++;
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];
                }

                // Mettre à jour le progrès
                $job->update([
                    'processed_rows' => $successCount + $failureCount,
                    'successful_rows' => $successCount,
                    'failed_rows' => $failureCount
                ]);
            }

            // Finaliser le job
            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
                'errors' => $errors
            ]);

            return [
                'success' => true,
                'processed' => $successCount + $failureCount,
                'successful' => $successCount,
                'failed' => $failureCount
            ];

        } catch (Exception $e) {
            $job->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => [['error' => $e->getMessage()]]
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Traiter un job d'export
     */
    public function processExportJob($job)
    {
        try {
            // Marquer le job comme en cours
            $job->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Récupérer les données selon le type et les filtres
            $data = $this->getExportData($job->type, $job->filters ?? [], $job->restaurant_id);

            $job->update(['total_rows' => count($data)]);

            // Générer le fichier
            $filePath = $this->generateExportFile($data, $job->format, $job->filename, $job->settings ?? []);

            // Finaliser le job
            $job->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'processed_rows' => count($data),
                'completed_at' => now(),
                'expires_at' => now()->addDays(7) // Expire après 7 jours
            ]);

            return [
                'success' => true,
                'file_path' => $filePath,
                'total_rows' => count($data)
            ];

        } catch (Exception $e) {
            $job->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les imports programmés
     */
    public function getScheduledImports()
    {
        return \App\Models\ImportJob::scheduled()->get();
    }

    /**
     * Obtenir les exports programmés
     */
    public function getScheduledExports()
    {
        return \App\Models\ExportJob::scheduled()->get();
    }

    /**
     * Obtenir les tâches récurrentes
     */
    public function getRecurringTasks()
    {
        // Pour l'instant, retourner une collection vide
        // Cela peut être étendu plus tard pour supporter les tâches récurrentes
        return collect([]);
    }

    /**
     * Traiter une tâche récurrente
     */
    public function processRecurringTask($task)
    {
        // Placeholder pour les tâches récurrentes
        return [
            'success' => true,
            'message' => 'Tâche récurrente traitée'
        ];
    }

    /**
     * Traiter une ligne d'import selon le type
     */
    private function processImportRow($type, $row, $restaurantId, $settings)
    {
        switch ($type) {
            case 'menus':
                return $this->importMenuItem($row, $restaurantId, $settings);
            case 'customers':
                return $this->importCustomer($row, $restaurantId, $settings);
            case 'orders':
                return $this->importOrder($row, $restaurantId, $settings);
            case 'categories':
                return $this->importCategory($row, $restaurantId, $settings);
            default:
                throw new Exception("Type d'import non supporté: {$type}");
        }
    }

    /**
     * Obtenir les données pour l'export
     */
    private function getExportData($type, $filters, $restaurantId)
    {
        switch ($type) {
            case 'menus':
                return $this->getMenuItemsForExport($filters, $restaurantId);
            case 'customers':
                return $this->getCustomersForExport($filters, $restaurantId);
            case 'orders':
                return $this->getOrdersForExport($filters, $restaurantId);
            case 'categories':
                return $this->getCategoriesForExport($filters, $restaurantId);
            default:
                throw new Exception("Type d'export non supporté: {$type}");
        }
    }

    /**
     * Lire les données d'un fichier
     */
    private function readFileData($filePath, $format)
    {
        $fullPath = Storage::path($filePath);

        switch ($format) {
            case 'csv':
                return $this->readCsvFile($fullPath);
            case 'xlsx':
            case 'xls':
                return $this->readExcelFile($fullPath);
            case 'json':
                return $this->readJsonFile($fullPath);
            default:
                throw new Exception("Format non supporté: {$format}");
        }
    }

    /**
     * Méthodes pour l'import de différents types
     */
    private function importMenuItem($row, $restaurantId, $settings)
    {
        $menuItem = \App\Models\MenuItem::updateOrCreate(
            [
                'name' => $row['name'],
                'restaurant_id' => $restaurantId
            ],
            [
                'description' => $row['description'] ?? '',
                'price' => (float) $row['price'],
                'category_id' => $row['category_id'],
                'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
                'is_available' => isset($row['is_available']) ? (bool) $row['is_available'] : true,
                'preparation_time' => $row['preparation_time'] ?? null,
                'calories' => $row['calories'] ?? null,
                'ingredients' => $row['ingredients'] ?? null,
                'allergens' => $row['allergens'] ?? null,
                'image' => $row['image'] ?? null,
            ]
        );

        return $menuItem->id;
    }

    private function importCustomer($row, $restaurantId, $settings)
    {
        // Vérifier si l'email existe déjà
        $existingUser = \App\Models\User::where('email', $row['email'])->first();

        if ($existingUser && $settings['update_existing'] ?? false) {
            $existingUser->update([
                'name' => $row['name'],
                'phone' => $row['phone'] ?? null,
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'address' => $row['address'] ?? null,
                'city' => $row['city'] ?? null,
                'postal_code' => $row['postal_code'] ?? null,
                'country' => $row['country'] ?? null,
            ]);
            return $existingUser->id;
        } elseif (!$existingUser) {
            $user = \App\Models\User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make(Str::random(8)), // Mot de passe temporaire
                'phone' => $row['phone'] ?? null,
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'address' => $row['address'] ?? null,
                'city' => $row['city'] ?? null,
                'postal_code' => $row['postal_code'] ?? null,
                'country' => $row['country'] ?? null,
                'email_verified_at' => now(),
                'is_active' => $row['is_active'] ?? true,
            ]);

            // Associer au restaurant si nécessaire
            if ($restaurantId && class_exists('\App\Models\RestaurantUser')) {
                \App\Models\RestaurantUser::create([
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurantId,
                    'role' => 'customer'
                ]);
            }

            return $user->id;
        }

        throw new Exception("Client avec email {$row['email']} existe déjà");
    }

    private function importOrder($row, $restaurantId, $settings)
    {
        // Récupérer le client
        $customer = \App\Models\User::where('email', $row['customer_email'])->first();
        if (!$customer) {
            throw new Exception("Client introuvable: {$row['customer_email']}");
        }

        $order = \App\Models\Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurantId,
            'total' => (float) $row['total'],
            'status' => $row['status'] ?? 'pending',
            'payment_method' => $row['payment_method'] ?? 'cash',
            'delivery_address' => $row['delivery_address'] ?? null,
            'notes' => $row['notes'] ?? null,
            'order_date' => $row['order_date'] ?? now(),
            'delivery_date' => $row['delivery_date'] ?? null,
        ]);

        // Traiter les articles si fournis
        if (isset($row['items']) && is_string($row['items'])) {
            $items = json_decode($row['items'], true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                    ]);
                }
            }
        }

        return $order->id;
    }

    private function importCategory($row, $restaurantId, $settings)
    {
        $category = \App\Models\Category::updateOrCreate(
            [
                'name' => $row['name'],
                'restaurant_id' => $restaurantId
            ],
            [
                'description' => $row['description'] ?? '',
                'sort_order' => $row['sort_order'] ?? 0,
                'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
                'parent_id' => $row['parent_id'] ?? null,
                'image' => $row['image'] ?? null,
            ]
        );

        return $category->id;
    }

    /**
     * Méthodes pour l'export de différents types
     */
    private function getMenuItemsForExport($filters, $restaurantId)
    {
        $query = \App\Models\MenuItem::where('restaurant_id', $restaurantId);

        // Appliquer les filtres
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with('category')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'category_id' => $item->category_id,
                'category_name' => $item->category->name ?? '',
                'is_active' => $item->is_active ? 1 : 0,
                'is_available' => $item->is_available ? 1 : 0,
                'preparation_time' => $item->preparation_time,
                'calories' => $item->calories,
                'ingredients' => $item->ingredients,
                'allergens' => $item->allergens,
                'image' => $item->image,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    private function getCustomersForExport($filters, $restaurantId)
    {
        $query = \App\Models\User::whereHas('restaurants', function($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        });

        // Appliquer les filtres
        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        return $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'address' => $user->address,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'country' => $user->country,
                'is_active' => $user->is_active ? 1 : 0,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    private function getOrdersForExport($filters, $restaurantId)
    {
        $query = \App\Models\Order::where('restaurant_id', $restaurantId);

        // Appliquer les filtres
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['min_total'])) {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (!empty($filters['max_total'])) {
            $query->where('total', '<=', $filters['max_total']);
        }

        return $query->with(['user', 'items.menuItem'])->get()->map(function ($order) {
            $items = $order->items->map(function($item) {
                return [
                    'name' => $item->menuItem->name ?? 'Produit supprimé',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ];
            });

            return [
                'id' => $order->id,
                'customer_name' => $order->user->name ?? 'Client supprimé',
                'customer_email' => $order->user->email ?? '',
                'total' => $order->total,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'delivery_address' => $order->delivery_address,
                'notes' => $order->notes,
                'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                'delivery_date' => $order->delivery_date?->format('Y-m-d H:i:s'),
                'items_count' => $order->items->count(),
                'items' => json_encode($items->toArray()),
            ];
        })->toArray();
    }

    private function getCategoriesForExport($filters, $restaurantId)
    {
        $query = \App\Models\Category::where('restaurant_id', $restaurantId);

        // Appliquer les filtres
        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        return $query->withCount('menuItems')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active ? 1 : 0,
                'parent_id' => $category->parent_id,
                'image' => $category->image,
                'menu_items_count' => $category->menu_items_count ?? 0,
                'created_at' => $category->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $category->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Générer un fichier d'export
     */
    private function generateExportFile($data, $format, $filename, $settings = [])
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = $filename ?: "export_{$timestamp}";
        $filePath = "exports/{$fileName}.{$format}";

        switch ($format) {
            case 'csv':
                $this->generateCsvFile($data, $filePath, $settings);
                break;
            case 'xlsx':
                $this->generateExcelFile($data, $filePath, $settings);
                break;
            case 'json':
                $this->generateJsonFile($data, $filePath, $settings);
                break;
            default:
                throw new Exception("Format d'export non supporté: {$format}");
        }

        return $filePath;
    }

    /**
     * Lire un fichier CSV
     */
    private function readCsvFile($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Lire un fichier Excel
     */
    private function readExcelFile($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        $rows = $worksheet->toArray();
        if (!empty($rows)) {
            $headers = array_shift($rows);
            foreach ($rows as $row) {
                $data[] = array_combine($headers, $row);
            }
        }

        return $data;
    }

    /**
     * Lire un fichier JSON
     */
    private function readJsonFile($filePath)
    {
        $content = file_get_contents($filePath);
        return json_decode($content, true) ?: [];
    }

    /**
     * Générer un fichier CSV
     */
    private function generateCsvFile($data, $filePath, $settings = [])
    {
        $fullPath = Storage::path($filePath);
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($fullPath, 'w');

        if (!empty($data)) {
            // Écrire les en-têtes
            fputcsv($handle, array_keys($data[0]));

            // Écrire les données
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }

        fclose($handle);
    }

    /**
     * Générer un fichier Excel
     */
    private function generateExcelFile($data, $filePath, $settings = [])
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        if (!empty($data)) {
            // En-têtes
            $headers = array_keys($data[0]);
            $worksheet->fromArray($headers, null, 'A1');

            // Données
            $worksheet->fromArray($data, null, 'A2');
        }

        $writer = new Xlsx($spreadsheet);
        $fullPath = Storage::path($filePath);
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->save($fullPath);
    }

    /**
     * Générer un fichier JSON
     */
    private function generateJsonFile($data, $filePath, $settings = [])
    {
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::put($filePath, $content);
    }
}
