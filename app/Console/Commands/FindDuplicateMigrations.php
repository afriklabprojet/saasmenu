<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindDuplicateMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrations:find-duplicates {--fix : Archive duplicate migrations automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find duplicate table creations in migration files';

    protected $tables = [];
    protected $duplicates = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analysing migrations...');
        $this->newLine();

        $migrationsPath = database_path('migrations');
        $files = File::glob($migrationsPath . '/*.php');
        
        $this->info("Found " . count($files) . " migration files");
        $this->newLine();

        // Parse all migrations
        foreach ($files as $file) {
            $this->parseMigrationFile($file);
        }

        // Display results
        $this->displayResults();

        // Fix if requested
        if ($this->option('fix')) {
            $this->fixDuplicates();
        }

        return 0;
    }

    protected function parseMigrationFile($filePath)
    {
        $fileName = basename($filePath);
        $content = File::get($filePath);

        // Find all Schema::create calls
        preg_match_all('/Schema::create\([\'"]([a-z_]+)[\'"]/i', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $tableName) {
                if (!isset($this->tables[$tableName])) {
                    $this->tables[$tableName] = [];
                }
                $this->tables[$tableName][] = $fileName;
            }
        }
    }

    protected function displayResults()
    {
        // Find duplicates
        foreach ($this->tables as $table => $migrations) {
            if (count($migrations) > 1) {
                $this->duplicates[$table] = $migrations;
            }
        }

        if (empty($this->duplicates)) {
            $this->info('✅ No duplicate table creations found!');
            return;
        }

        $this->error('❌ DUPLICATES DETECTED:');
        $this->newLine();

        foreach ($this->duplicates as $table => $migrations) {
            $this->warn("Table '{$table}': " . count($migrations) . " migrations");
            
            foreach ($migrations as $index => $migration) {
                $marker = $index === 0 ? '✓ KEEP' : '✗ ARCHIVE';
                $color = $index === 0 ? 'green' : 'yellow';
                $this->line("  - {$migration} <fg={$color}>{$marker}</>");
            }
            
            $this->newLine();
        }

        $this->info('📊 Summary:');
        $this->info("  Total tables: " . count($this->tables));
        $this->info("  Unique tables: " . (count($this->tables) - count($this->duplicates)));
        $this->info("  Duplicate tables: " . count($this->duplicates));
        $this->info("  Files to archive: " . array_sum(array_map(fn($m) => count($m) - 1, $this->duplicates)));
        $this->newLine();

        if (!$this->option('fix')) {
            $this->comment('💡 Run with --fix to archive duplicate migrations automatically');
        }
    }

    protected function fixDuplicates()
    {
        if (empty($this->duplicates)) {
            return;
        }

        if (!$this->confirm('Are you sure you want to archive duplicate migrations?')) {
            $this->info('Cancelled.');
            return;
        }

        $archivePath = database_path('migrations/archived_duplicates');
        
        if (!File::exists($archivePath)) {
            File::makeDirectory($archivePath, 0755, true);
            $this->info("Created archive directory: {$archivePath}");
        }

        $archived = 0;

        foreach ($this->duplicates as $table => $migrations) {
            // Keep first migration, archive others
            foreach (array_slice($migrations, 1) as $migration) {
                $sourcePath = database_path('migrations/' . $migration);
                $destPath = $archivePath . '/' . $migration;

                if (File::exists($sourcePath)) {
                    File::move($sourcePath, $destPath);
                    $this->info("✓ Archived: {$migration}");
                    $archived++;
                }
            }
        }

        $this->newLine();
        $this->info("✅ Successfully archived {$archived} duplicate migrations");
        $this->comment("📁 Location: {$archivePath}");
        $this->newLine();
        $this->info("🧪 You can now run: php artisan migrate:fresh --seed");
    }
}
