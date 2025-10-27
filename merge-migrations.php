<?php

/**
 * Script pour fusionner toutes les migrations Laravel en une seule
 * Ne conserve que les instructions create_table
 */

$migrationsPath = __DIR__ . '/database/migrations';
$outputFile = __DIR__ . '/database/migrations/2024_01_01_000000_create_all_tables.php';

// Fonction pour extraire le contenu de la méthode up()
function extractUpMethod($content) {
    // Chercher la méthode up()
    if (preg_match('/public function up\(\)[\s\S]*?\{([\s\S]*?)(?=public function down|$)/i', $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

// Fonction pour extraire les instructions create_table
function extractCreateTableStatements($upContent) {
    $statements = [];

    // Pattern pour Schema::create
    if (preg_match_all('/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\([^)]*\)\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/s', $upContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableDefinition = trim($match[2]);

            // Nettoyer la définition
            $tableDefinition = preg_replace('/^\s*\$table\s*=\s*[^;]*;\s*/m', '', $tableDefinition);
            $tableDefinition = preg_replace('/\s+/', ' ', $tableDefinition);
            $tableDefinition = trim($tableDefinition);

            if (!empty($tableDefinition)) {
                $statements[] = [
                    'table' => $tableName,
                    'definition' => $tableDefinition
                ];
            }
        }
    }

    return $statements;
}

// Scanner tous les fichiers de migration
$allTables = [];
$migrationFiles = glob($migrationsPath . '/*.php');

echo "Analyse de " . count($migrationFiles) . " fichiers de migration...\n";

foreach ($migrationFiles as $file) {
    $filename = basename($file);

    // Ignorer certains types de migrations
    if (strpos($filename, 'update_') !== false ||
        strpos($filename, 'modify_') !== false ||
        strpos($filename, 'add_') !== false ||
        strpos($filename, 'drop_') !== false ||
        strpos($filename, 'alter_') !== false ||
        strpos($filename, 'seed_') !== false) {
        continue;
    }

    $content = file_get_contents($file);
    $upContent = extractUpMethod($content);

    if (!empty($upContent)) {
        $createStatements = extractCreateTableStatements($upContent);

        foreach ($createStatements as $statement) {
            $tableName = $statement['table'];

            // Éviter les doublons
            if (!isset($allTables[$tableName])) {
                $allTables[$tableName] = $statement['definition'];
                echo "✓ Table trouvée: $tableName (depuis $filename)\n";
            } else {
                echo "⚠ Table en double ignorée: $tableName (depuis $filename)\n";
            }
        }
    }
}

echo "\nTotal de " . count($allTables) . " tables uniques trouvées.\n";

// Générer la migration fusionnée
$migrationContent = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration fusionnée de toutes les tables
     * Générée automatiquement le ' . date('Y-m-d H:i:s') . '
     */
    public function up()
    {
';

// Ajouter chaque table
foreach ($allTables as $tableName => $definition) {
    $migrationContent .= "        // Table: $tableName\n";
    $migrationContent .= "        Schema::create('$tableName', function (Blueprint \$table) {\n";

    // Reformater la définition
    $lines = explode(';', $definition);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && $line !== '}') {
            $migrationContent .= "            $line;\n";
        }
    }

    $migrationContent .= "        });\n\n";
}

$migrationContent .= '    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
';

// Ajouter les drops dans l'ordre inverse
$tableNames = array_reverse(array_keys($allTables));
foreach ($tableNames as $tableName) {
    $migrationContent .= "        Schema::dropIfExists('$tableName');\n";
}

$migrationContent .= '    }
};
';

// Sauvegarder la migration fusionnée
file_put_contents($outputFile, $migrationContent);

echo "\n✅ Migration fusionnée créée: " . basename($outputFile) . "\n";
echo "📊 Statistiques:\n";
echo "   - Fichiers analysés: " . count($migrationFiles) . "\n";
echo "   - Tables créées: " . count($allTables) . "\n";
echo "   - Tables:\n";

foreach (array_keys($allTables) as $table) {
    echo "     • $table\n";
}

echo "\n🔧 Prochaines étapes:\n";
echo "1. Vérifier le fichier généré: database/migrations/2024_01_01_000000_create_all_tables.php\n";
echo "2. Sauvegarder vos migrations actuelles\n";
echo "3. Supprimer les anciennes migrations (optionnel)\n";
echo "4. Tester la nouvelle migration\n";
