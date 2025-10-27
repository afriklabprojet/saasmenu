<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_export_jobs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['import', 'export']);
            $table->string('data_type'); // menus, products, customers, orders, etc.
            $table->string('file_path')->nullable(); // Chemin du fichier source (import)
            $table->string('export_file_path')->nullable(); // Chemin du fichier généré (export)
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('format')->nullable(); // csv, xlsx, json, pdf
            $table->json('mapping')->nullable(); // Mapping des champs
            $table->json('filters')->nullable(); // Filtres pour l'export
            $table->json('options')->nullable(); // Options diverses
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('errors')->nullable(); // Erreurs rencontrées
            $table->json('warnings')->nullable(); // Avertissements
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('progress', 5, 2)->default(0); // Pourcentage de progression
            $table->timestamp('estimated_completion')->nullable();
            $table->bigInteger('file_size')->nullable(); // Taille du fichier en bytes
            $table->integer('memory_usage')->nullable(); // Utilisation mémoire en MB
            $table->integer('execution_time')->nullable(); // Temps d'exécution en secondes
            $table->json('metadata')->nullable(); // Métadonnées diverses
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['data_type', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('import_export_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('data_type'); // Type de données (menus, products, etc.)
            $table->json('source_fields'); // Champs sources disponibles
            $table->json('target_fields'); // Champs cibles disponibles
            $table->json('field_mappings'); // Mapping source -> cible
            $table->json('transformations')->nullable(); // Transformations à appliquer
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->boolean('is_default')->default(false); // Mapping par défaut pour ce type
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('usage_count')->default(0); // Nombre d'utilisations
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['data_type', 'is_active']);
            $table->index(['is_default', 'data_type']);
            $table->index('user_id');
        });

        Schema::create('import_export_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['import', 'export']); // Type de template
            $table->string('data_type'); // Type de données
            $table->string('format'); // Format du fichier (csv, xlsx, json)
            $table->json('fields'); // Configuration des champs
            $table->json('sample_data')->nullable(); // Données d'exemple
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->json('transformations')->nullable(); // Transformations par défaut
            $table->boolean('is_system')->default(false); // Template système
            $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0); // Nombre de téléchargements
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'data_type']);
            $table->index(['format', 'is_active']);
            $table->index('is_system');
            $table->index('user_id');
        });

        Schema::create('import_export_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['import', 'export']);
            $table->string('data_type');
            $table->string('format')->nullable();
            $table->string('frequency'); // daily, weekly, monthly, custom
            $table->string('cron_expression')->nullable(); // Expression cron pour custom
            $table->json('options')->nullable(); // Options d'import/export
            $table->json('filters')->nullable(); // Filtres pour export
            $table->string('file_path')->nullable(); // Chemin du fichier pour import
            $table->string('export_destination')->nullable(); // Destination pour export
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->json('last_result')->nullable(); // Résultat de la dernière exécution
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('next_run_at');
            $table->index('user_id');
        });

        Schema::create('import_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->nullable()->constrained('import_export_jobs')->onDelete('set null');
            $table->enum('level', ['info', 'warning', 'error', 'debug']);
            $table->string('message');
            $table->text('details')->nullable();
            $table->json('context')->nullable(); // Contexte supplémentaire
            $table->string('file')->nullable(); // Fichier source de l'erreur
            $table->integer('line')->nullable(); // Ligne source de l'erreur
            $table->timestamp('logged_at');
            $table->json('metadata')->nullable();

            $table->index(['job_id', 'level']);
            $table->index(['level', 'logged_at']);
            $table->index('logged_at');
        });

        Schema::create('import_export_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name'); // Nom original du fichier
            $table->string('stored_name'); // Nom stocké
            $table->string('path'); // Chemin de stockage
            $table->string('disk')->default('local'); // Disque de stockage
            $table->string('mime_type');
            $table->bigInteger('size'); // Taille en bytes
            $table->string('hash')->nullable(); // Hash MD5/SHA1
            $table->enum('type', ['import_source', 'export_result', 'template', 'sample']);
            $table->string('data_type')->nullable(); // Type de données
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('job_id')->nullable()->constrained('import_export_jobs')->onDelete('set null');
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // Date d'expiration
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'data_type']);
            $table->index('user_id');
            $table->index('job_id');
            $table->index('expires_at');
            $table->index('hash');
        });

        Schema::create('import_export_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du mapping
            $table->string('data_type'); // Type de données
            $table->string('source_field'); // Champ source
            $table->string('target_field'); // Champ cible
            $table->json('transformation_rules')->nullable(); // Règles de transformation
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->string('default_value')->nullable(); // Valeur par défaut
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->foreignId('mapping_id')->constrained('import_export_mappings')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['mapping_id', 'sort_order']);
            $table->index(['data_type', 'target_field']);
        });

        Schema::create('import_export_transformations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la transformation
            $table->text('description')->nullable();
            $table->string('type'); // Type de transformation (replace, regex, format, etc.)
            $table->json('parameters'); // Paramètres de la transformation
            $table->string('input_type')->nullable(); // Type d'entrée attendu
            $table->string('output_type')->nullable(); // Type de sortie produit
            $table->text('example_input')->nullable(); // Exemple d'entrée
            $table->text('example_output')->nullable(); // Exemple de sortie
            $table->boolean('is_system')->default(false); // Transformation système
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('usage_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('is_system');
            $table->index('user_id');
        });

        Schema::create('import_export_validation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la règle
            $table->text('description')->nullable();
            $table->string('field_type'); // Type de champ (string, number, date, etc.)
            $table->string('rule_type'); // Type de règle (required, min, max, regex, etc.)
            $table->json('parameters')->nullable(); // Paramètres de la règle
            $table->string('error_message'); // Message d'erreur
            $table->text('example')->nullable(); // Exemple de validation
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('severity')->default(1); // 1=error, 2=warning, 3=info
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['field_type', 'rule_type']);
            $table->index(['is_system', 'is_active']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_export_validation_rules');
        Schema::dropIfExists('import_export_transformations');
        Schema::dropIfExists('import_export_field_mappings');
        Schema::dropIfExists('import_export_files');
        Schema::dropIfExists('import_export_logs');
        Schema::dropIfExists('import_export_schedules');
        Schema::dropIfExists('import_export_templates');
        Schema::dropIfExists('import_export_mappings');
        Schema::dropIfExists('import_export_jobs');
    }
};
