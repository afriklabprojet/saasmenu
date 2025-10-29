<?php

/**
 * 🔍 Script d'Audit Technique RestroSaaS
 *
 * Ce script génère un rapport complet d'audit technique du projet
 */

class TechnicalAuditTool
{
    private $basePath;
    private $results = [];

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function runAudit()
    {
        echo "🔍 AUDIT TECHNIQUE RESTROSAAS\n";
        echo "=====================================\n\n";

        $this->checkControllers();
        $this->checkStorageLinks();
        $this->checkEnvUsage();
        $this->checkTranslations();
        $this->checkBundlerConfig();
        $this->checkRoutes();
        $this->generateReport();
    }

    private function checkControllers()
    {
        echo "📋 1. Vérification des contrôleurs...\n";

        $adminControllers = glob($this->basePath . '/app/Http/Controllers/Admin/*Controller.php');
        $this->results['controllers'] = [
            'admin_controllers_found' => count($adminControllers),
            'controllers' => array_map('basename', $adminControllers)
        ];

        echo "   ✅ " . count($adminControllers) . " contrôleurs Admin trouvés\n\n";
    }

    private function checkStorageLinks()
    {
        echo "📁 2. Vérification des liens de storage...\n";

        $publicStorage = $this->basePath . '/public/storage';
        $storagePublic = $this->basePath . '/storage/app/public';

        $linkExists = is_link($publicStorage);
        $storageExists = is_dir($storagePublic);

        $this->results['storage'] = [
            'symlink_exists' => $linkExists,
            'storage_public_exists' => $storageExists,
            'status' => $linkExists && $storageExists ? 'OK' : 'WARNING'
        ];

        echo "   " . ($linkExists ? "✅" : "❌") . " Lien symbolique public/storage\n";
        echo "   " . ($storageExists ? "✅" : "❌") . " Dossier storage/app/public\n\n";
    }

    private function checkEnvUsage()
    {
        echo "⚙️ 3. Vérification de l'utilisation d'env()...\n";

        // Chercher les utilisations d'env() hors du dossier config
        $command = "grep -r \"env('\" " . $this->basePath . " --exclude-dir=config --include=\"*.php\" 2>/dev/null | wc -l";
        $envUsageCount = (int)trim(shell_exec($command));

        $this->results['env_usage'] = [
            'env_calls_outside_config' => $envUsageCount,
            'status' => $envUsageCount > 5 ? 'WARNING' : 'OK'
        ];

        echo "   " . ($envUsageCount <= 5 ? "✅" : "⚠️") . " {$envUsageCount} utilisations d'env() hors config\n\n";
    }

    private function checkTranslations()
    {
        echo "🌐 4. Vérification des traductions...\n";

        $langDirs = glob($this->basePath . '/resources/lang/*', GLOB_ONLYDIR);
        $languages = array_map('basename', $langDirs);

        $this->results['translations'] = [
            'languages_available' => $languages,
            'languages_count' => count($languages)
        ];

        echo "   ✅ " . count($languages) . " langues disponibles: " . implode(', ', $languages) . "\n\n";
    }

    private function checkBundlerConfig()
    {
        echo "📦 5. Vérification de la configuration bundler...\n";

        $viteExists = file_exists($this->basePath . '/vite.config.js');
        $webpackExists = file_exists($this->basePath . '/webpack.mix.js');
        $packageJson = file_exists($this->basePath . '/package.json');

        $this->results['bundler'] = [
            'vite_config' => $viteExists,
            'webpack_config' => $webpackExists,
            'package_json' => $packageJson,
            'recommended_bundler' => $viteExists ? 'Vite' : 'None'
        ];

        echo "   " . ($viteExists ? "✅" : "❌") . " Configuration Vite\n";
        echo "   " . ($webpackExists ? "⚠️" : "✅") . " Configuration Webpack (devrait être supprimée)\n";
        echo "   " . ($packageJson ? "✅" : "❌") . " Package.json\n\n";
    }

    private function checkRoutes()
    {
        echo "🛣️ 6. Vérification des routes...\n";

        $webRoutes = file_exists($this->basePath . '/routes/web.php');
        $apiRoutes = file_exists($this->basePath . '/routes/api.php');

        $this->results['routes'] = [
            'web_routes' => $webRoutes,
            'api_routes' => $apiRoutes
        ];

        echo "   " . ($webRoutes ? "✅" : "❌") . " Routes web\n";
        echo "   " . ($apiRoutes ? "✅" : "❌") . " Routes API\n\n";
    }

    private function generateReport()
    {
        echo "📊 RAPPORT D'AUDIT\n";
        echo "==================\n\n";

        // Score global
        $totalChecks = 0;
        $passedChecks = 0;

        // Controllers
        if (count($this->results['controllers']['controllers']) > 0) {
            $passedChecks++;
        }
        $totalChecks++;

        // Storage
        if ($this->results['storage']['status'] === 'OK') {
            $passedChecks++;
        }
        $totalChecks++;

        // Env usage
        if ($this->results['env_usage']['status'] === 'OK') {
            $passedChecks++;
        }
        $totalChecks++;

        // Translations
        if ($this->results['translations']['languages_count'] >= 2) {
            $passedChecks++;
        }
        $totalChecks++;

        // Bundler
        if ($this->results['bundler']['vite_config'] && !$this->results['bundler']['webpack_config']) {
            $passedChecks++;
        }
        $totalChecks++;

        // Routes
        if ($this->results['routes']['web_routes']) {
            $passedChecks++;
        }
        $totalChecks++;

        $score = round(($passedChecks / $totalChecks) * 100);

        echo "🎯 Score global: {$score}% ({$passedChecks}/{$totalChecks})\n\n";

        // Recommandations
        echo "💡 RECOMMANDATIONS PRIORITAIRES:\n";
        echo "=================================\n";

        if ($this->results['storage']['status'] !== 'OK') {
            echo "🔴 URGENT: Corriger les liens de storage\n";
        }

        if ($this->results['env_usage']['status'] !== 'OK') {
            echo "🟠 IMPORTANT: Réduire l'utilisation d'env() hors config\n";
        }

        if ($this->results['bundler']['webpack_config']) {
            echo "🟡 MOYEN: Supprimer la configuration Webpack inutilisée\n";
        }

        if ($this->results['translations']['languages_count'] < 2) {
            echo "🟢 AMÉLIORATION: Ajouter plus de langues\n";
        }

        echo "\n✅ Audit terminé avec succès!\n";

        // Sauvegarde du rapport
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'score' => $score,
            'results' => $this->results
        ];

        file_put_contents($this->basePath . '/TECHNICAL_AUDIT_REPORT.json', json_encode($reportData, JSON_PRETTY_PRINT));
        echo "📄 Rapport détaillé sauvegardé: TECHNICAL_AUDIT_REPORT.json\n";
    }
}

// Exécution de l'audit
$basePath = __DIR__;
$audit = new TechnicalAuditTool($basePath);
$audit->runAudit();
