<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    protected $defaultOptions = [
        'size' => 200,
        'margin' => 2,
        'format' => 'png',
        'errorCorrection' => 'M',
        'encoding' => 'UTF-8',
    ];

    /**
     * Générer un QR code en base64
     */
    public function generate($data, $options = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $options);

            $qrCode = QrCode::format($options['format'])
                ->size($options['size'])
                ->margin($options['margin'])
                ->errorCorrection($options['errorCorrection'])
                ->encoding($options['encoding'])
                ->generate($data);

            return 'data:image/' . $options['format'] . ';base64,' . base64_encode($qrCode);

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Générer une image QR code
     */
    public function generateImage($data, $options = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $options);

            return QrCode::format($options['format'])
                ->size($options['size'])
                ->margin($options['margin'])
                ->errorCorrection($options['errorCorrection'])
                ->encoding($options['encoding'])
                ->generate($data);

        } catch (\Exception $e) {
            Log::error('Erreur génération image QR Code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sauvegarder le QR code sur le disque
     */
    public function saveToFile($data, $filename, $options = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $options);
            $disk = $options['disk'] ?? 'public';
            $path = $options['path'] ?? 'qrcodes';

            $qrImage = $this->generateImage($data, $options);

            if (!$qrImage) {
                return false;
            }

            $fullPath = $path . '/' . $filename . '.' . $options['format'];
            Storage::disk($disk)->put($fullPath, $qrImage);

            return $fullPath;

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde QR Code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un QR code pour une table
     */
    public function generateForTable($table, $options = [])
    {
        if (!$table->restaurant) {
            return null;
        }

        $url = route('table.menu', [
            'restaurant_slug' => $table->restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $defaultOptions = [
            'size' => 300,
            'margin' => 3,
        ];

        return $this->generate($url, array_merge($defaultOptions, $options));
    }

    /**
     * Générer et sauvegarder le QR code d'une table
     */
    public function generateAndSaveForTable($table, $options = [])
    {
        if (!$table->restaurant) {
            return false;
        }

        $url = route('table.menu', [
            'restaurant_slug' => $table->restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $filename = "table-{$table->restaurant->id}-{$table->table_number}";
        $defaultOptions = [
            'size' => 400,
            'margin' => 4,
            'path' => 'qrcodes/tables',
        ];

        return $this->saveToFile($url, $filename, array_merge($defaultOptions, $options));
    }

    /**
     * Générer un QR code avec logo personnalisé
     */
    public function generateWithLogo($data, $logoPath, $options = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $options);
            $logoSize = $options['logo_size'] ?? ($options['size'] * 0.2);

            $qrCode = QrCode::format($options['format'])
                ->size($options['size'])
                ->margin($options['margin'])
                ->errorCorrection('H') // Plus haute correction pour supporter le logo
                ->encoding($options['encoding'])
                ->merge($logoPath, $logoSize / $options['size'], true)
                ->generate($data);

            return 'data:image/' . $options['format'] . ';base64,' . base64_encode($qrCode);

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code avec logo: ' . $e->getMessage());
            // Fallback sans logo
            return $this->generate($data, $options);
        }
    }

    /**
     * Générer un QR code avec couleurs personnalisées
     */
    public function generateWithColors($data, $foreground = '#000000', $background = '#FFFFFF', $options = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $options);

            // Convertir les couleurs hex en RGB
            $fgRgb = $this->hexToRgb($foreground);
            $bgRgb = $this->hexToRgb($background);

            $qrCode = QrCode::format($options['format'])
                ->size($options['size'])
                ->margin($options['margin'])
                ->errorCorrection($options['errorCorrection'])
                ->encoding($options['encoding'])
                ->color($fgRgb[0], $fgRgb[1], $fgRgb[2])
                ->backgroundColor($bgRgb[0], $bgRgb[1], $bgRgb[2])
                ->generate($data);

            return 'data:image/' . $options['format'] . ';base64,' . base64_encode($qrCode);

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code avec couleurs: ' . $e->getMessage());
            return $this->generate($data, $options);
        }
    }

    /**
     * Générer un QR code avec logo ET couleurs personnalisées
     */
    public function generateCustom($data, $customOptions = [])
    {
        try {
            $options = array_merge($this->defaultOptions, $customOptions);

            $qrBuilder = QrCode::format($options['format'])
                ->size($options['size'])
                ->margin($options['margin'])
                ->errorCorrection($options['errorCorrection'] ?? 'H')
                ->encoding($options['encoding']);

            // Appliquer les couleurs si spécifiées
            if (isset($customOptions['foreground_color']) && isset($customOptions['background_color'])) {
                $fgRgb = $this->hexToRgb($customOptions['foreground_color']);
                $bgRgb = $this->hexToRgb($customOptions['background_color']);

                $qrBuilder->color($fgRgb[0], $fgRgb[1], $fgRgb[2])
                          ->backgroundColor($bgRgb[0], $bgRgb[1], $bgRgb[2]);
            }

            // Appliquer le logo si spécifié
            if (isset($customOptions['logo_path']) && file_exists($customOptions['logo_path'])) {
                $logoSize = $customOptions['logo_size'] ?? ($options['size'] * 0.2);
                $qrBuilder->merge($customOptions['logo_path'], $logoSize / $options['size'], true);
            }

            $qrCode = $qrBuilder->generate($data);

            return 'data:image/' . $options['format'] . ';base64,' . base64_encode($qrCode);

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code personnalisé: ' . $e->getMessage());
            return $this->generate($data, $options);
        }
    }

    /**
     * Convertir couleur hex en RGB
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Générer un QR code avec le logo du restaurant
     */
    public function generateForTableWithLogo($table, $options = [])
    {
        if (!$table->restaurant) {
            return null;
        }

        $url = route('table.menu', [
            'restaurant_slug' => $table->restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $logoPath = $table->restaurant->logo_path;

        if ($logoPath && Storage::exists($logoPath)) {
            return $this->generateWithLogo($url, Storage::path($logoPath), $options);
        }

        return $this->generate($url, $options);
    }

    /**
     * Valider un QR code
     */
    public function validate($qrData)
    {
        try {
            // Vérifier si c'est une URL valide de notre application
            if (!filter_var($qrData, FILTER_VALIDATE_URL)) {
                return false;
            }

            // Vérifier si l'URL correspond à notre pattern de table
            $pattern = '/\/table\/([a-zA-Z0-9\-_]+)\/([A-Z0-9]+)$/';
            return preg_match($pattern, parse_url($qrData, PHP_URL_PATH)) === 1;

        } catch (\Exception $e) {
            Log::error('Erreur validation QR Code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extraire les informations d'un QR code de table
     */
    public function extractTableInfo($qrData)
    {
        try {
            $pattern = '/\/table\/([a-zA-Z0-9\-_]+)\/([A-Z0-9]+)$/';
            $path = parse_url($qrData, PHP_URL_PATH);

            if (preg_match($pattern, $path, $matches)) {
                return [
                    'restaurant_slug' => $matches[1],
                    'table_code' => $matches[2],
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Erreur extraction info QR Code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Générer plusieurs QR codes en lot
     */
    public function generateBatch($dataArray, $options = [])
    {
        $results = [];

        foreach ($dataArray as $key => $data) {
            $results[$key] = $this->generate($data, $options);
        }

        return $results;
    }

    /**
     * Créer un PDF avec plusieurs QR codes
     */
    public function generatePDF($tables, $options = [])
    {
        try {
            // Vérifier si TCPDF est disponible
            if (!class_exists('TCPDF')) {
                Log::warning('TCPDF non disponible. Installer avec: composer require tecnickcom/tcpdf');
                return [
                    'success' => false,
                    'message' => 'TCPDF non installé. Veuillez exécuter: composer require tecnickcom/tcpdf'
                ];
            }

            // Configuration PDF
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->SetCreator('E-menu');
            $pdf->SetAuthor('E-menu System');
            $pdf->SetTitle('QR Codes Tables');
            $pdf->SetSubject('QR Codes pour Tables de Restaurant');

            // Désactiver header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);

            // 6 QR codes par page (2 colonnes x 3 lignes)
            $qrPerPage = 6;
            $qrSize = 60; // Taille en mm
            $spacing = 10; // Espacement en mm
            $textHeight = 20; // Hauteur pour le texte

            $colWidth = ($pdf->getPageWidth() - 30) / 2;
            $rowHeight = $qrSize + $textHeight + $spacing;

            $count = 0;

            foreach ($tables as $table) {
                if ($count % $qrPerPage === 0) {
                    $pdf->AddPage();
                }

                $position = $count % $qrPerPage;
                $col = $position % 2;
                $row = floor($position / 2);

                $x = 10 + ($col * ($colWidth + $spacing));
                $y = 10 + ($row * $rowHeight);

                // Générer le QR code
                $qrUrl = route('table.menu', [
                    'restaurant_slug' => $table->restaurant->slug,
                    'table_code' => $table->table_code
                ]);

                $customOptions = array_merge([
                    'size' => 300,
                    'margin' => 1,
                ], $options);

                // Ajouter logo si disponible
                if (isset($table->restaurant->logo_path) && Storage::exists($table->restaurant->logo_path)) {
                    $customOptions['logo_path'] = Storage::path($table->restaurant->logo_path);
                }

                $qrImage = $this->generateImage($qrUrl, $customOptions);

                if ($qrImage) {
                    // Sauvegarder temporairement le QR
                    $tempFile = tempnam(sys_get_temp_dir(), 'qr_');
                    file_put_contents($tempFile, $qrImage);

                    // Ajouter le QR au PDF
                    $pdf->Image($tempFile, $x, $y, $qrSize, $qrSize, 'PNG');

                    // Ajouter les informations de la table
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->SetXY($x, $y + $qrSize + 2);
                    $pdf->Cell($qrSize, 8, 'Table ' . $table->table_number, 0, 1, 'C');

                    $pdf->SetFont('helvetica', '', 10);
                    if ($table->name) {
                        $pdf->SetX($x);
                        $pdf->Cell($qrSize, 6, $table->name, 0, 1, 'C');
                    }

                    if ($table->location) {
                        $pdf->SetX($x);
                        $pdf->SetFont('helvetica', 'I', 9);
                        $pdf->Cell($qrSize, 5, $table->location, 0, 1, 'C');
                    }

                    unlink($tempFile);
                }

                $count++;
            }

            // Sauvegarder le PDF
            $filename = 'qr-tables-' . now()->format('Y-m-d-H-i-s') . '.pdf';
            $pdfPath = 'qrcodes/batch/' . $filename;

            Storage::put($pdfPath, $pdf->Output('', 'S'));

            Log::info('PDF QR codes généré avec succès: ' . count($tables) . ' tables');

            return [
                'success' => true,
                'file_path' => $pdfPath,
                'download_url' => Storage::url($pdfPath),
                'filename' => $filename,
                'tables_count' => count($tables),
                'message' => 'PDF généré avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF QR codes: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Créer un ZIP avec plusieurs QR codes
     */
    public function generateZip($tables, $options = [])
    {
        try {
            $zipPath = 'qrcodes/batch/tables-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $zip = new \ZipArchive();

            $tempFile = tempnam(sys_get_temp_dir(), 'qr_zip');

            if ($zip->open($tempFile, \ZipArchive::CREATE) === TRUE) {

                foreach ($tables as $table) {
                    $qrImage = $this->generateImage($table->qr_url, $options);
                    if ($qrImage) {
                        $filename = "table-{$table->table_number}.png";
                        $zip->addFromString($filename, $qrImage);
                    }
                }

                $zip->close();

                // Sauvegarder le ZIP
                Storage::put($zipPath, file_get_contents($tempFile));
                unlink($tempFile);

                return [
                    'success' => true,
                    'file_path' => $zipPath,
                    'download_url' => Storage::url($zipPath),
                    'message' => 'Archive ZIP créée avec succès'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'archive'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération ZIP QR codes: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du ZIP'
            ];
        }
    }

    /**
     * Nettoyer les anciens QR codes
     */
    public function cleanupOldFiles($days = 30)
    {
        try {
            $cutoff = now()->subDays($days);
            $files = Storage::allFiles('qrcodes');
            $deletedCount = 0;

            foreach ($files as $file) {
                if (Storage::lastModified($file) < $cutoff->timestamp) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }

            Log::info("Nettoyage QR codes: {$deletedCount} fichiers supprimés");
            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Erreur nettoyage QR codes: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Enregistrer un scan de QR code
     */
    public function recordScan($table, $request = null)
    {
        try {
            $scanData = [
                'table_id' => $table->id,
                'restaurant_id' => $table->restaurant_id,
                'scanned_at' => now(),
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'referrer' => $request ? $request->header('referer') : null,
            ];

            DB::table('table_qr_scans')->insert($scanData);

            // Incrémenter le compteur de scans
            $table->increment('scan_count');
            $table->update(['last_scanned_at' => now()]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur enregistrement scan QR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les statistiques de scan pour une table
     */
    public function getScanStats($tableId, $period = 'week')
    {
        try {
            $query = DB::table('table_qr_scans')
                ->where('table_id', $tableId);

            switch ($period) {
                case 'today':
                    $query->whereDate('scanned_at', today());
                    break;
                case 'week':
                    $query->where('scanned_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('scanned_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('scanned_at', '>=', now()->subYear());
                    break;
            }

            $totalScans = $query->count();
            $uniqueIps = $query->distinct('ip_address')->count('ip_address');

            $scansByHour = $query->select(
                DB::raw('HOUR(scanned_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

            return [
                'total_scans' => $totalScans,
                'unique_visitors' => $uniqueIps,
                'scans_by_hour' => $scansByHour,
                'period' => $period,
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération stats scan QR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir les statistiques de scan pour un restaurant
     */
    public function getRestaurantScanStats($restaurantId, $period = 'week')
    {
        try {
            $query = DB::table('table_qr_scans')
                ->where('restaurant_id', $restaurantId);

            switch ($period) {
                case 'today':
                    $query->whereDate('scanned_at', today());
                    break;
                case 'week':
                    $query->where('scanned_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('scanned_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('scanned_at', '>=', now()->subYear());
                    break;
            }

            $totalScans = $query->count();
            $uniqueIps = $query->distinct('ip_address')->count('ip_address');

            $topTables = $query->select(
                'table_id',
                DB::raw('COUNT(*) as scan_count')
            )
            ->groupBy('table_id')
            ->orderByDesc('scan_count')
            ->limit(10)
            ->get();

            $scansByDate = $query->select(
                DB::raw('DATE(scanned_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            return [
                'total_scans' => $totalScans,
                'unique_visitors' => $uniqueIps,
                'top_tables' => $topTables,
                'scans_by_date' => $scansByDate,
                'period' => $period,
                'average_per_day' => $scansByDate->avg('count') ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération stats restaurant scan QR: ' . $e->getMessage());
            return null;
        }
    }
}
