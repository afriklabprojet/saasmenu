<?php

/**
 * 🧠 AI-Assisted Laravel Migration Refactoring Tool
 * 
 * Ce script analyse toutes les migrations existantes et génère des migrations
 * propres avec Schema::create() pour chaque table unique.
 */

class AdvancedMigrationRefactorer
{
    private $migrationsPath;
    private $outputPath;
    private $tableSchemas = [];
    private $processedTables = [];
    
    public function __construct($basePath)
    {
        $this->migrationsPath = $basePath . '/database/migrations';
        $this->outputPath = $basePath . '/database/migrations_ai_refactored';
        
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }
    
    public function run()
    {
        echo "🧠 AI Migration Refactoring Tool\n";
        echo "=====================================\n\n";
        
        $this->analyzeMigrations();
        $this->generateCleanMigrations();
        
        echo "\n✅ Refactorisation IA terminée!\n";
        echo "📊 " . count($this->tableSchemas) . " tables analysées\n";
        echo "📁 Migrations générées dans: {$this->outputPath}\n";
    }
    
    private function analyzeMigrations()
    {
        $files = glob($this->migrationsPath . '/*.php');
        echo "📁 Trouvé " . count($files) . " fichiers de migration\n\n";
        
        foreach ($files as $file) {
            $this->analyzeMigrationFile($file);
        }
    }
    
    private function analyzeMigrationFile($file)
    {
        $filename = basename($file);
        echo "🔎 Analyse: $filename\n";
        
        $content = file_get_contents($file);
        
        // Extract table operations
        $this->extractTableOperations($content, $filename);
    }
    
    private function extractTableOperations($content, $filename)
    {
        // Pattern for Schema::create
        if (preg_match('/Schema::create\([\'"]([^\'\"]+)[\'"],.*?function.*?\{(.*?)\}\);/s', $content, $matches)) {
            $tableName = $matches[1];
            $tableDefinition = $matches[2];
            
            if (!isset($this->tableSchemas[$tableName])) {
                $this->tableSchemas[$tableName] = [
                    'columns' => [],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'source_files' => []
                ];
            }
            
            $this->tableSchemas[$tableName]['source_files'][] = $filename;
            $this->parseTableDefinition($tableDefinition, $tableName);
        }
        
        // Pattern for Schema::table (modifications)
        if (preg_match_all('/Schema::table\([\'"]([^\'\"]+)[\'"],.*?function.*?\{(.*?)\}\);/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableName = $match[1];
                $tableDefinition = $match[2];
                
                if (!isset($this->tableSchemas[$tableName])) {
                    $this->tableSchemas[$tableName] = [
                        'columns' => [],
                        'indexes' => [],
                        'foreign_keys' => [],
                        'source_files' => []
                    ];
                }
                
                $this->tableSchemas[$tableName]['source_files'][] = $filename;
                $this->parseTableDefinition($tableDefinition, $tableName, true);
            }
        }
    }
    
    private function parseTableDefinition($definition, $tableName, $isModification = false)
    {
        $lines = explode("\n", $definition);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '//') === 0) continue;
            
            // Parse column definitions
            if (preg_match('/\$table->(\w+)\([\'"]([^\'\"]+)[\'"].*?\)/', $line, $matches)) {
                $columnType = $matches[1];
                $columnName = $matches[2];
                
                // Skip certain operations
                if (in_array($columnType, ['dropColumn', 'dropIndex', 'dropForeign'])) {
                    continue;
                }
                
                if (!isset($this->tableSchemas[$tableName]['columns'][$columnName])) {
                    $this->tableSchemas[$tableName]['columns'][$columnName] = [
                        'type' => $columnType,
                        'definition' => $line,
                        'nullable' => strpos($line, '->nullable()') !== false,
                        'default' => $this->extractDefault($line),
                        'unique' => strpos($line, '->unique()') !== false,
                        'comment' => $this->extractComment($line)
                    ];
                }
            }
            
            // Parse indexes
            if (preg_match('/\$table->index\((.*?)\)/', $line, $matches)) {
                $this->tableSchemas[$tableName]['indexes'][] = $line;
            }
            
            // Parse foreign keys
            if (preg_match('/\$table->foreign\((.*?)\)/', $line, $matches)) {
                $this->tableSchemas[$tableName]['foreign_keys'][] = $line;
            }
        }
    }
    
    private function extractDefault($line)
    {
        if (preg_match('/->default\((.*?)\)/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function extractComment($line)
    {
        if (preg_match('/->comment\([\'"]([^\'\"]+)[\'\"]\)/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function generateCleanMigrations()
    {
        echo "\n🎯 Génération des migrations propres...\n\n";
        
        $counter = 1;
        foreach ($this->tableSchemas as $tableName => $schema) {
            $this->generateTableMigration($tableName, $schema, $counter);
            $counter++;
        }
    }
    
    private function generateTableMigration($tableName, $schema, $counter)
    {
        $timestamp = '2025_01_01_' . str_pad($counter, 6, '0', STR_PAD_LEFT);
        $filename = "{$timestamp}_create_{$tableName}_table.php";
        $filepath = $this->outputPath . '/' . $filename;
        
        $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Table';
        
        $content = $this->generateMigrationContent($tableName, $schema, $className);
        
        file_put_contents($filepath, $content);
        echo "📝 Généré: $filename\n";
    }
    
    private function generateMigrationContent($tableName, $schema, $className)
    {
        $sourceFiles = implode(', ', $schema['source_files']);
        $purpose = $this->getTablePurpose($tableName);
        
        $content = "<?php\n\n";
        $content .= "use Illuminate\Database\Migrations\Migration;\n";
        $content .= "use Illuminate\Database\Schema\Blueprint;\n";
        $content .= "use Illuminate\Support\Facades\Schema;\n\n";
        $content .= "return new class extends Migration\n{\n";
        $content .= "    /**\n";
        $content .= "     * Run the migrations.\n";
        $content .= "     * \n";
        $content .= "     * Table: {$tableName}\n";
        $content .= "     * Purpose: {$purpose}\n";
        $content .= "     * Original migrations: {$sourceFiles}\n";
        $content .= "     */\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
        
        // Add common columns first
        if (isset($schema['columns']['id'])) {
            $content .= "            \$table->id();\n";
        }
        
        // Add other columns
        foreach ($schema['columns'] as $columnName => $columnInfo) {
            if ($columnName === 'id') continue; // Already added
            
            $columnDef = $this->generateColumnDefinition($columnName, $columnInfo);
            $content .= "            {$columnDef}\n";
        }
        
        // Add timestamps if they exist
        if (isset($schema['columns']['created_at']) || isset($schema['columns']['updated_at'])) {
            $content .= "            \$table->timestamps();\n";
        }
        
        // Add indexes
        if (!empty($schema['indexes'])) {
            $content .= "\n            // Indexes\n";
            foreach ($schema['indexes'] as $index) {
                $content .= "            {$index}\n";
            }
        }
        
        // Add foreign keys
        if (!empty($schema['foreign_keys'])) {
            $content .= "\n            // Foreign keys\n";
            foreach ($schema['foreign_keys'] as $foreignKey) {
                $content .= "            {$foreignKey}\n";
            }
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
        
        return $content;
    }
    
    private function generateColumnDefinition($columnName, $columnInfo)
    {
        $definition = "\$table->{$columnInfo['type']}('{$columnName}')";
        
        if ($columnInfo['nullable']) {
            $definition .= "->nullable()";
        }
        
        if ($columnInfo['default'] !== null) {
            $definition .= "->default({$columnInfo['default']})";
        }
        
        if ($columnInfo['unique']) {
            $definition .= "->unique()";
        }
        
        if ($columnInfo['comment']) {
            $definition .= "->comment('{$columnInfo['comment']}')";
        }
        
        $definition .= ";";
        
        return $definition;
    }
    
    private function getTablePurpose($tableName)
    {
        $purposes = [
            'users' => 'Store user accounts (admins, restaurant owners, staff)',
            'customers' => 'Store customer information and profiles',
            'restaurants' => 'Store restaurant/vendor information',
            'orders' => 'Store customer orders from restaurants',
            'order_details' => 'Store individual items within orders',
            'order_items' => 'Store order line items with quantities and prices',
            'items' => 'Store menu items/products for restaurants',
            'categories' => 'Store item categories for organizing menus',
            'tables' => 'Store restaurant table information for dining',
            'bookings' => 'Store table reservations and bookings',
            'payments' => 'Store payment transaction records',
            'payment_methods' => 'Store available payment gateway configurations',
            'carts' => 'Store shopping cart items for customers',
            'favorites' => 'Store customer favorite items',
            'wishlists' => 'Store customer wishlist items',
            'loyalty_programs' => 'Store loyalty program configurations',
            'loyalty_members' => 'Store customer loyalty program memberships',
            'loyalty_transactions' => 'Store loyalty points earned/spent transactions',
            'loyalty_rewards' => 'Store available loyalty rewards',
            'loyalty_redemptions' => 'Store loyalty reward redemption records',
            'notifications' => 'Store system notifications for users',
            'settings' => 'Store application and vendor-specific settings',
            'banners' => 'Store promotional banners and advertisements',
            'promocodes' => 'Store promotional discount codes',
            'coupons' => 'Store discount coupons and offers',
            'subscribers' => 'Store newsletter subscribers',
            'contacts' => 'Store customer contact/support messages',
            'blogs' => 'Store blog posts and articles',
            'languages' => 'Store multi-language support configurations',
            'translations' => 'Store translated text content',
            'pos_terminals' => 'Store POS terminal configurations',
            'pos_sessions' => 'Store POS login sessions and shifts',
            'pos_carts' => 'Store POS cart items during transactions',
        ];
        
        return $purposes[$tableName] ?? 'Store ' . str_replace('_', ' ', $tableName) . ' data';
    }
}

// Run the refactorer
$basePath = __DIR__;
$refactorer = new AdvancedMigrationRefactorer($basePath);
$refactorer->run();