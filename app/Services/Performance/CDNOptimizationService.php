<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CDN & Asset Optimization Service
 * Optimise les assets, images et gère la distribution via CDN
 */
class CDNOptimizationService
{
    private $cdnUrl;
    private $compressionQuality = 85;
    private $webpEnabled = true;
    private $lazyLoadEnabled = true;

    public function __construct()
    {
        $this->cdnUrl = config('app.cdn_url', config('app.url'));
    }

    /**
     * Optimise toutes les images d'un répertoire
     */
    public function optimizeImages(string $directory = 'public'): array
    {
        $results = [
            'processed' => 0,
            'optimized' => 0,
            'errors' => 0,
            'size_saved' => 0
        ];

        try {
            $files = Storage::allFiles($directory);
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (in_array($extension, $imageExtensions)) {
                    $results['processed']++;

                    $optimized = $this->optimizeImage($file);
                    if ($optimized) {
                        $results['optimized']++;
                        $results['size_saved'] += $optimized['size_saved'] ?? 0;
                    } else {
                        $results['errors']++;
                    }
                }
            }

            Log::info('Image optimization completed', $results);
            return $results;

        } catch (\Exception $e) {
            Log::error('Image optimization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Optimise une image spécifique
     */
    public function optimizeImage(string $imagePath): ?array
    {
        try {
            $fullPath = storage_path('app/' . $imagePath);

            if (!file_exists($fullPath)) {
                return null;
            }

            $originalSize = filesize($fullPath);
            $imageInfo = getimagesize($fullPath);

            if (!$imageInfo) {
                return null;
            }

            // Optimiser avec GD si disponible
            if (extension_loaded('gd')) {
                $optimized = $this->optimizeImageWithGD($fullPath, $imageInfo);
                if ($optimized) {
                    $optimizedSize = filesize($fullPath);
                    $sizeSaved = $originalSize - $optimizedSize;

                    return [
                        'original_size' => $originalSize,
                        'optimized_size' => $optimizedSize,
                        'size_saved' => $sizeSaved,
                        'compression_ratio' => round(($sizeSaved / $originalSize) * 100, 2)
                    ];
                }
            }

            // Fallback: compression basique
            return [
                'original_size' => $originalSize,
                'optimized_size' => $originalSize,
                'size_saved' => 0,
                'compression_ratio' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Failed to optimize image: ' . $imagePath . ' - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Optimise une image avec la librairie GD
     */
    private function optimizeImageWithGD(string $fullPath, array $imageInfo): bool
    {
        try {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $type = $imageInfo[2];

            // Créer l'image source
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($fullPath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($fullPath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($fullPath);
                    break;
                default:
                    return false;
            }

            if (!$source) {
                return false;
            }

            // Redimensionner si nécessaire
            if ($width > 1920) {
                $newWidth = 1920;
                $newHeight = (int)($height * ($newWidth / $width));

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            // Sauvegarder avec compression
            $success = false;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $success = imagejpeg($source, $fullPath, $this->compressionQuality);
                    break;
                case IMAGETYPE_PNG:
                    $success = imagepng($source, $fullPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    $success = imagegif($source, $fullPath);
                    break;
            }

            // Créer version WebP si activé
            if ($this->webpEnabled && function_exists('imagewebp')) {
                $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $fullPath);
                imagewebp($source, $webpPath, 85);
            }

            imagedestroy($source);
            return $success;

        } catch (\Exception $e) {
            Log::error('GD image optimization failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Génère des images responsive
     */
    public function generateResponsiveImages(string $imagePath): array
    {
        $sizes = [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
            'xlarge' => [1920, 1920]
        ];

        $generated = [];

        try {
            $fullPath = storage_path('app/' . $imagePath);
            $imageInfo = getimagesize($fullPath);

            if (!$imageInfo || !extension_loaded('gd')) {
                return [];
            }

            $pathInfo = pathinfo($imagePath);

            foreach ($sizes as $sizeName => $dimensions) {
                $newPath = $pathInfo['dirname'] . '/' .
                          $pathInfo['filename'] . '_' . $sizeName . '.' .
                          $pathInfo['extension'];

                $newFullPath = storage_path('app/' . $newPath);

                if ($this->createResizedImage($fullPath, $newFullPath, $dimensions, $imageInfo)) {
                    $newImageInfo = getimagesize($newFullPath);

                    $generated[$sizeName] = [
                        'path' => $newPath,
                        'url' => $this->getCDNUrl($newPath),
                        'width' => $newImageInfo[0] ?? $dimensions[0],
                        'height' => $newImageInfo[1] ?? $dimensions[1]
                    ];
                }
            }

            return $generated;

        } catch (\Exception $e) {
            Log::error('Failed to generate responsive images: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée une image redimensionnée avec GD
     */
    private function createResizedImage(string $sourcePath, string $destPath, array $dimensions, array $imageInfo): bool
    {
        try {
            $sourceWidth = $imageInfo[0];
            $sourceHeight = $imageInfo[1];
            $type = $imageInfo[2];

            // Calculer nouvelles dimensions en gardant les proportions
            $targetWidth = $dimensions[0];
            $targetHeight = $dimensions[1];

            $ratio = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);

            // Créer l'image source
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($sourcePath);
                    break;
                default:
                    return false;
            }

            if (!$source) {
                return false;
            }

            // Créer l'image de destination
            $destination = imagecreatetruecolor($newWidth, $newHeight);

            // Préserver la transparence pour PNG
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }

            // Redimensionner
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            // Créer le répertoire si nécessaire
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            // Sauvegarder
            $success = false;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $success = imagejpeg($destination, $destPath, $this->compressionQuality);
                    break;
                case IMAGETYPE_PNG:
                    $success = imagepng($destination, $destPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    $success = imagegif($destination, $destPath);
                    break;
            }

            imagedestroy($source);
            imagedestroy($destination);

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to create resized image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Génère les URLs CDN pour les assets
     */
    public function getCDNUrl(string $assetPath): string
    {
        // Nettoyer le path
        $assetPath = ltrim($assetPath, '/');

        // Ajouter versioning pour cache busting
        $version = $this->getAssetVersion($assetPath);

        return rtrim($this->cdnUrl, '/') . '/' . $assetPath . '?v=' . $version;
    }

    /**
     * Génère une version hash pour le cache busting
     */
    private function getAssetVersion(string $assetPath): string
    {
        return Cache::remember('asset_version_' . md5($assetPath), 3600, function () use ($assetPath) {
            $fullPath = public_path($assetPath);

            if (file_exists($fullPath)) {
                return substr(md5_file($fullPath), 0, 8);
            }

            return substr(md5($assetPath), 0, 8);
        });
    }

    /**
     * Minifie les fichiers CSS
     */
    public function minifyCSS(string $cssContent): string
    {
        // Supprimer commentaires
        $cssContent = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $cssContent);

        // Supprimer espaces inutiles
        $cssContent = str_replace(["\r\n", "\r", "\n", "\t"], '', $cssContent);
        $cssContent = preg_replace('/\s+/', ' ', $cssContent);

        // Supprimer espaces autour des caractères spéciaux
        $cssContent = str_replace([' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;'],
                                 ['{', '{', '}', '}', ':', ':', ';', ';'], $cssContent);

        return trim($cssContent);
    }

    /**
     * Minifie les fichiers JavaScript
     */
    public function minifyJS(string $jsContent): string
    {
        // Supprimer commentaires simple ligne
        $jsContent = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $jsContent);

        // Supprimer espaces multiples
        $jsContent = preg_replace('/\s+/', ' ', $jsContent);

        // Supprimer espaces autour des opérateurs
        $jsContent = str_replace([' = ', ' + ', ' - ', ' * ', ' / ', ' && ', ' || '],
                                ['=', '+', '-', '*', '/', '&&', '||'], $jsContent);

        return trim($jsContent);
    }

    /**
     * Créer bundle combiné de fichiers CSS
     */
    public function createCSSBundle(array $cssFiles, string $bundleName = 'app'): string
    {
        $bundleContent = '';
        $bundlePath = "css/bundles/{$bundleName}.min.css";

        foreach ($cssFiles as $cssFile) {
            $fullPath = public_path($cssFile);
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $bundleContent .= $this->minifyCSS($content) . "\n";
            }
        }

        // Sauvegarder le bundle
        $bundleFullPath = public_path($bundlePath);
        $bundleDir = dirname($bundleFullPath);

        if (!is_dir($bundleDir)) {
            mkdir($bundleDir, 0755, true);
        }

        file_put_contents($bundleFullPath, $bundleContent);

        return $this->getCDNUrl($bundlePath);
    }

    /**
     * Créer bundle combiné de fichiers JavaScript
     */
    public function createJSBundle(array $jsFiles, string $bundleName = 'app'): string
    {
        $bundleContent = '';
        $bundlePath = "js/bundles/{$bundleName}.min.js";

        foreach ($jsFiles as $jsFile) {
            $fullPath = public_path($jsFile);
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $bundleContent .= $this->minifyJS($content) . ";\n";
            }
        }

        // Sauvegarder le bundle
        $bundleFullPath = public_path($bundlePath);
        $bundleDir = dirname($bundleFullPath);

        if (!is_dir($bundleDir)) {
            mkdir($bundleDir, 0755, true);
        }

        file_put_contents($bundleFullPath, $bundleContent);

        return $this->getCDNUrl($bundlePath);
    }

    /**
     * Génère le HTML pour image responsive avec lazy loading
     */
    public function generateResponsiveImageHTML(string $imagePath, string $alt = '', array $classes = []): string
    {
        $responsiveImages = $this->generateResponsiveImages($imagePath);

        if (empty($responsiveImages)) {
            return '<img src="' . $this->getCDNUrl($imagePath) . '" alt="' . htmlspecialchars($alt) . '">';
        }

        $srcset = [];
        foreach ($responsiveImages as $size => $image) {
            $srcset[] = $image['url'] . ' ' . $image['width'] . 'w';
        }

        $classes[] = 'lazy-load';
        $classAttr = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';

        return sprintf(
            '<img src="%s" data-srcset="%s" sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw" alt="%s"%s loading="lazy">',
            $responsiveImages['thumbnail']['url'],
            implode(', ', $srcset),
            htmlspecialchars($alt),
            $classAttr
        );
    }

    /**
     * Préchauffe le cache des assets critiques
     */
    public function warmupAssetCache(): array
    {
        $criticalAssets = [
            'admin-assets/css/bootstrap.min.css',
            'admin-assets/css/style.css',
            'admin-assets/js/jquery.min.js',
            'admin-assets/js/bootstrap.bundle.min.js'
        ];

        $warmed = [];

        foreach ($criticalAssets as $asset) {
            $version = $this->getAssetVersion($asset);
            $url = $this->getCDNUrl($asset);

            Cache::put('asset_cache_' . md5($asset), $url, 3600);
            $warmed[] = $url;
        }

        return $warmed;
    }

    /**
     * Nettoie les caches d'assets obsolètes
     */
    public function cleanAssetCache(): int
    {
        try {
            $store = Cache::getStore();
            $deleted = 0;

            if (method_exists($store, 'flush')) {
                // Pour file cache, on peut vider complètement
                Cache::flush();
                $deleted = 1;
            } else {
                // Fallback pour autres drivers
                $keys = ['asset_version_*', 'asset_cache_*', 'critical_css_*'];
                foreach ($keys as $pattern) {
                    Cache::forget($pattern);
                    $deleted++;
                }
            }

            Log::info("Cleaned {$deleted} asset cache entries");
            return $deleted;

        } catch (\Exception $e) {
            Log::warning("Failed to clean asset cache: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Statistiques de performance des assets
     */
    public function getPerformanceStats(): array
    {
        return [
            'cdn_enabled' => $this->cdnUrl !== config('app.url'),
            'webp_enabled' => $this->webpEnabled,
            'compression_quality' => $this->compressionQuality,
            'cache_entries' => $this->countCacheEntries(),
            'optimized_images' => $this->countOptimizedImages(),
            'bundle_files' => $this->countBundleFiles()
        ];
    }

    private function countCacheEntries(): int
    {
        try {
            // Estimer le nombre d'entrées de cache assets
            $cacheDir = storage_path('framework/cache/data');
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*asset*');
                return count($files);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function countOptimizedImages(): int
    {
        try {
            $webpFiles = [];
            $publicPath = storage_path('app/public');

            if (is_dir($publicPath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($publicPath)
                );

                foreach ($iterator as $file) {
                    if ($file->getExtension() === 'webp') {
                        $webpFiles[] = $file;
                    }
                }
            }

            return count($webpFiles);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function countBundleFiles(): int
    {
        try {
            $cssBundles = [];
            $jsBundles = [];

            $cssBundlePath = public_path('css/bundles');
            if (is_dir($cssBundlePath)) {
                $cssBundles = glob($cssBundlePath . '/*.min.css');
            }

            $jsBundlePath = public_path('js/bundles');
            if (is_dir($jsBundlePath)) {
                $jsBundles = glob($jsBundlePath . '/*.min.js');
            }

            return count($cssBundles) + count($jsBundles);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
