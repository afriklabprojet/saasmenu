<?php

/**
 * Script de refactorisation des migrations Laravel
 * Analyse toutes les migrations et créé des migrations fusionnées par table
 */

class MigrationRefactorer
{
    private $migrationsPath;
    private $outputPath;
    private $tableSchemas = [];
    private $processedFiles = [];
    
    public function __construct($migrationsPath, $outputPath)
    {
        $this->migrationsPath = $migrationsPath;
        $this->outputPath = $outputPath;
        
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }
    
    public function refactor()
    {
        echo "🔍 Analyse des migrations dans: {$this->migrationsPath}\n";
        
        // 1. Scanner tous les fichiers de migration
        $migrationFiles = $this->scanMigrationFiles();
        echo "📁 Trouvé " . count($migrationFiles) . " fichiers de migration\n";
        
        // 2. Analyser chaque fichier
        foreach ($migrationFiles as $file) {
            $this->analyzeMigrationFile($file);
        }
        
        // 3. Générer les migrations refactorisées
        $this->generateRefactoredMigrations();
        
        echo "\n✅ Refactorisation terminée!\n";
        echo "📊 " . count($this->tableSchemas) . " tables traitées\n";
        echo "📁 Migrations générées dans: {$this->outputPath}\n";
    }
    
    private function scanMigrationFiles()
    {
        return glob($this->migrationsPath . '/*.php');
    }
    
    private function analyzeMigrationFile($filePath)
    {
        $filename = basename($filePath);
        
        // Ignorer la migration fusionnée existante
        if (strpos($filename, 'create_all_tables') !== false) {
            return;
        }
        
        echo "🔎 Analyse: $filename\n";
        
        $content = file_get_contents($filePath);
        
        // Extraire le nom de la table
        $tableName = $this->extractTableName($filename, $content);
        
        if (!$tableName) {
            echo "   ⚠️  Impossible de déterminer le nom de la table\n";
            return;
        }
        
        // Analyser le contenu de la migration
        $this->analyzeTableSchema($tableName, $content, $filename);
        
        $this->processedFiles[] = $filename;
    }
    
    private function extractTableName($filename, $content)
    {
        // Method 1: From filename
        if (preg_match('/create_([a-z_]+)_table\.php$/', $filename, $matches)) {
            return $matches[1];
        }
        
        // Method 2: From update/add pattern
        if (preg_match('/(?:update|add|modify)_([a-z_]+)_table\.php$/', $filename, $matches)) {
            return $matches[1];
        }
        
        // Method 3: From Schema::create/table calls
        if (preg_match('/Schema::(?:create|table)\s*\(\s*[\'"]([^\'\"]+)[\'"]/', $content, $matches)) {
            return $matches[1];
        }
        
        // Method 4: From add_X_to_Y_table pattern
        if (preg_match('/add_[a-z_]+_to_([a-z_]+)_table\.php$/', $filename, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    private function analyzeTableSchema($tableName, $content, $filename)
    {
        if (!isset($this->tableSchemas[$tableName])) {
            $this->tableSchemas[$tableName] = [
                'columns' => [],
                'indexes' => [],
                'foreign_keys' => [],
                'files' => []
            ];
        }
        
        $this->tableSchemas[$tableName]['files'][] = $filename;
        
        // Analyser Schema::create
        if (preg_match('/Schema::create\s*\(\s*[\'"]' . preg_quote($tableName) . '[\'"]\s*,\s*function\s*\([^)]*\)\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/s', $content, $matches)) {
            $this->parseTableDefinition($tableName, $matches[1]);
        }
        
        // Analyser Schema::table (pour les modifications)
        if (preg_match_all('/Schema::table\s*\(\s*[\'"]' . preg_quote($tableName) . '[\'"]\s*,\s*function\s*\([^)]*\)\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $this->parseTableDefinition($tableName, $match[1]);
            }
        }
    }
    
    private function parseTableDefinition($tableName, $definition)
    {
        // Nettoyer la définition
        $lines = explode("\n", $definition);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '//') === 0) continue;
            
            // Parser les colonnes
            if (preg_match('/\$table->([a-zA-Z]+)\s*\(([^)]*)\)([^;]*);/', $line, $matches)) {
                $type = $matches[1];
                $params = $matches[2];
                $modifiers = $matches[3];
                
                $this->addColumn($tableName, $type, $params, $modifiers);
            }
            
            // Parser les index
            if (preg_match('/\$table->(?:index|unique|primary)\s*\(([^)]*)\)/', $line, $matches)) {
                $this->addIndex($tableName, $line);
            }
            
            // Parser les foreign keys
            if (preg_match('/\$table->foreign\s*\(([^)]*)\)/', $line, $matches)) {
                $this->addForeignKey($tableName, $line);
            }
        }
    }
    
    private function addColumn($tableName, $type, $params, $modifiers)
    {
        // Extraire le nom de la colonne
        $paramsList = array_map('trim', explode(',', $params));
        $columnName = trim($paramsList[0], '\'"');
        
        if (empty($columnName)) return;
        
        $column = [
            'type' => $type,
            'params' => $params,
            'modifiers' => $modifiers,
            'definition' => "\$table->{$type}({$params}){$modifiers};"
        ];
        
        // Éviter les doublons
        $this->tableSchemas[$tableName]['columns'][$columnName] = $column;
    }
    
    private function addIndex($tableName, $line)
    {
        $this->tableSchemas[$tableName]['indexes'][] = $line;
    }
    
    private function addForeignKey($tableName, $line)
    {
        $this->tableSchemas[$tableName]['foreign_keys'][] = $line;
    }
    
    private function generateRefactoredMigrations()
    {
        $counter = 1;
        
        foreach ($this->tableSchemas as $tableName => $schema) {
            $timestamp = date('Y_m_d') . '_' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            $filename = "{$timestamp}_create_{$tableName}_table.php";
            $filepath = $this->outputPath . '/' . $filename;
            
            $this->generateMigrationFile($tableName, $schema, $filepath);
            
            echo "📝 Généré: $filename\n";
            $counter++;
        }
    }
    
    private function generateMigrationFile($tableName, $schema, $filepath)
    {
        $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'Table';
        
        $content = "<?php\n\n";
        $content .= "use Illuminate\\Database\\Migrations\\Migration;\n";
        $content .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
        $content .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
        $content .= "return new class extends Migration\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the migrations.\n";
        $content .= "     * \n";
        $content .= "     * Refactored migration for table: {$tableName}\n";
        $content .= "     * Original files: " . implode(', ', $schema['files']) . "\n";
        $content .= "     */\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
        
        // Ajouter les colonnes
        foreach ($schema['columns'] as $columnName => $column) {
            $content .= "            {$column['definition']}\n";
        }
        
        // Ajouter les index
        foreach ($schema['indexes'] as $index) {
            $content .= "            {$index}\n";
        }
        
        // Ajouter les foreign keys
        foreach ($schema['foreign_keys'] as $fk) {
            $content .= "            {$fk}\n";
        }
        
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    /**\n";
        $content .= "     * Reverse the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::dropIfExists('{$tableName}');\n";
        $content .= "    }\n";
        $content .= "};\n";
        
        file_put_contents($filepath, $content);
    }
}

// Configuration
$migrationsPath = __DIR__ . '/database/migrations';
$outputPath = __DIR__ . '/database/migrations_refactored';

// Exécution
$refactorer = new MigrationRefactorer($migrationsPath, $outputPath);
$refactorer->refactor();