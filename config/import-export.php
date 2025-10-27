<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Import/Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système d'import/export de données.
    | Permet l'importation et l'exportation de menus, produits, utilisateurs,
    | commandes avec support multi-format et validation avancée.
    |
    */

    // Configuration générale
    'enabled' => env('IMPORT_EXPORT_ENABLED', true),
    'debug' => env('IMPORT_EXPORT_DEBUG', false),

    // Formats supportés
    'supported_formats' => [
        'import' => ['csv', 'xlsx', 'xls', 'json', 'xml'],
        'export' => ['csv', 'xlsx', 'json', 'pdf', 'xml'],
    ],

    // Types de données supportés
    'supported_data_types' => [
        'menus' => [
            'label' => 'Menus et Plats',
            'description' => 'Import/export des menus, plats et items du restaurant',
            'icon' => 'fas fa-utensils',
            'model' => 'App\Models\Menu',
            'enabled' => true,
        ],
        'products' => [
            'label' => 'Produits',
            'description' => 'Import/export des produits et articles',
            'icon' => 'fas fa-box',
            'model' => 'App\Models\Product',
            'enabled' => true,
        ],
        'customers' => [
            'label' => 'Clients',
            'description' => 'Import/export des données clients',
            'icon' => 'fas fa-users',
            'model' => 'App\Models\Customer',
            'enabled' => true,
        ],
        'orders' => [
            'label' => 'Commandes',
            'description' => 'Import/export des commandes et historique',
            'icon' => 'fas fa-shopping-cart',
            'model' => 'App\Models\Order',
            'enabled' => true,
        ],
        'categories' => [
            'label' => 'Catégories',
            'description' => 'Import/export des catégories de produits',
            'icon' => 'fas fa-tags',
            'model' => 'App\Models\Category',
            'enabled' => true,
        ],
        'restaurants' => [
            'label' => 'Restaurants',
            'description' => 'Import/export des données restaurants',
            'icon' => 'fas fa-store',
            'model' => 'App\Models\Restaurant',
            'enabled' => true,
        ],
        'coupons' => [
            'label' => 'Coupons',
            'description' => 'Import/export des coupons et promotions',
            'icon' => 'fas fa-ticket-alt',
            'model' => 'App\Models\Coupon',
            'enabled' => true,
        ],
        'inventory' => [
            'label' => 'Inventaire',
            'description' => 'Import/export des données d\'inventaire',
            'icon' => 'fas fa-warehouse',
            'model' => 'App\Models\Inventory',
            'enabled' => true,
        ],
    ],

    // Limites et quotas
    'limits' => [
        'max_file_size' => env('IMPORT_EXPORT_MAX_FILE_SIZE', 50 * 1024 * 1024), // 50MB
        'max_records_per_import' => env('IMPORT_EXPORT_MAX_RECORDS', 10000),
        'max_concurrent_jobs' => env('IMPORT_EXPORT_MAX_JOBS', 5),
        'chunk_size' => env('IMPORT_EXPORT_CHUNK_SIZE', 100),
        'memory_limit' => env('IMPORT_EXPORT_MEMORY_LIMIT', '512M'),
        'execution_time_limit' => env('IMPORT_EXPORT_TIME_LIMIT', 300), // 5 minutes
        'max_exports_per_hour' => env('IMPORT_EXPORT_MAX_EXPORTS_HOUR', 10),
        'max_imports_per_hour' => env('IMPORT_EXPORT_MAX_IMPORTS_HOUR', 5),
    ],

    // Configuration des fichiers
    'files' => [
        'storage_disk' => env('IMPORT_EXPORT_DISK', 'local'),
        'temp_path' => env('IMPORT_EXPORT_TEMP_PATH', 'imports/temp'),
        'import_path' => env('IMPORT_EXPORT_IMPORT_PATH', 'imports'),
        'export_path' => env('IMPORT_EXPORT_EXPORT_PATH', 'exports'),
        'template_path' => env('IMPORT_EXPORT_TEMPLATE_PATH', 'templates'),
        'cleanup_after_days' => env('IMPORT_EXPORT_CLEANUP_DAYS', 30),
        'compress_exports' => env('IMPORT_EXPORT_COMPRESS', true),
        'encryption' => env('IMPORT_EXPORT_ENCRYPT', false),
    ],

    // Configuration de validation
    'validation' => [
        'enabled' => true,
        'strict_mode' => env('IMPORT_EXPORT_STRICT_VALIDATION', false),
        'max_errors_per_file' => env('IMPORT_EXPORT_MAX_ERRORS', 100),
        'stop_on_first_error' => env('IMPORT_EXPORT_STOP_ON_ERROR', false),
        'validate_references' => true, // Valider les clés étrangères
        'sanitize_input' => true, // Nettoyer les entrées
        'custom_rules' => [], // Règles de validation personnalisées
    ],

    // Configuration des transformations
    'transformations' => [
        'enabled' => true,
        'auto_detect_encoding' => true,
        'default_encoding' => 'UTF-8',
        'trim_whitespace' => true,
        'normalize_line_endings' => true,
        'convert_empty_to_null' => true,
        'date_formats' => [
            'Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y',
            'm/d/Y', 'Y/m/d', 'd.m.Y', 'Y.m.d'
        ],
        'decimal_separator' => '.',
        'thousands_separator' => ',',
    ],

    // Configuration des mappings par défaut
    'default_mappings' => [
        'menus' => [
            'name' => ['nom', 'title', 'titre', 'libelle', 'designation'],
            'description' => ['desc', 'details', 'infos'],
            'price' => ['prix', 'tarif', 'cout', 'montant'],
            'category' => ['categorie', 'type', 'famille'],
            'image' => ['photo', 'img', 'picture'],
            'is_active' => ['actif', 'active', 'enabled', 'statut'],
        ],
        'customers' => [
            'name' => ['nom', 'fullname', 'nom_complet', 'client'],
            'first_name' => ['prenom', 'firstname'],
            'last_name' => ['nom_famille', 'lastname', 'surname'],
            'email' => ['mail', 'e-mail', 'adresse_email'],
            'phone' => ['telephone', 'tel', 'mobile', 'gsm'],
            'address' => ['adresse', 'rue', 'domicile'],
            'city' => ['ville', 'commune', 'localite'],
            'postal_code' => ['cp', 'code_postal', 'zip'],
            'country' => ['pays', 'nation'],
        ],
        'products' => [
            'name' => ['nom', 'title', 'libelle', 'designation'],
            'sku' => ['reference', 'ref', 'code', 'numero'],
            'price' => ['prix', 'tarif', 'cout'],
            'stock' => ['quantite', 'qty', 'stock_qty'],
            'category' => ['categorie', 'type', 'famille'],
            'weight' => ['poids', 'masse'],
            'description' => ['desc', 'details'],
        ],
    ],

    // Configuration des templates
    'templates' => [
        'auto_generate' => true,
        'include_sample_data' => true,
        'sample_rows' => 3,
        'include_validation_info' => true,
        'localized' => true,
        'custom_templates' => [
            // Templates personnalisés par type
        ],
    ],

    // Configuration des jobs et queues
    'queue' => [
        'enabled' => env('IMPORT_EXPORT_QUEUE_ENABLED', true),
        'connection' => env('IMPORT_EXPORT_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('IMPORT_EXPORT_QUEUE_NAME', 'import-export'),
        'retry_after' => env('IMPORT_EXPORT_RETRY_AFTER', 300), // 5 minutes
        'max_tries' => env('IMPORT_EXPORT_MAX_TRIES', 3),
        'timeout' => env('IMPORT_EXPORT_TIMEOUT', 600), // 10 minutes
        'batch_size' => env('IMPORT_EXPORT_BATCH_SIZE', 50),
    ],

    // Configuration des notifications
    'notifications' => [
        'enabled' => env('IMPORT_EXPORT_NOTIFICATIONS', true),
        'channels' => ['mail', 'database'], // mail, database, slack, etc.
        'notify_on' => [
            'import_completed' => true,
            'import_failed' => true,
            'export_completed' => true,
            'export_failed' => true,
            'large_import_started' => true, // > 1000 records
        ],
        'email_template' => 'import-export.notifications.email',
        'slack_webhook' => env('IMPORT_EXPORT_SLACK_WEBHOOK', ''),
    ],

    // Configuration des logs
    'logging' => [
        'enabled' => env('IMPORT_EXPORT_LOGGING', true),
        'level' => env('IMPORT_EXPORT_LOG_LEVEL', 'info'),
        'channel' => env('IMPORT_EXPORT_LOG_CHANNEL', 'stack'),
        'log_queries' => env('IMPORT_EXPORT_LOG_QUERIES', false),
        'log_file_operations' => true,
        'log_transformations' => false,
        'retention_days' => env('IMPORT_EXPORT_LOG_RETENTION', 30),
    ],

    // Configuration de sécurité
    'security' => [
        'scan_uploads' => env('IMPORT_EXPORT_SCAN_UPLOADS', true),
        'allowed_mime_types' => [
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/json',
            'text/xml',
            'application/xml',
        ],
        'max_filename_length' => 255,
        'sanitize_filenames' => true,
        'encrypt_sensitive_data' => env('IMPORT_EXPORT_ENCRYPT_SENSITIVE', false),
        'hash_algorithm' => 'sha256',
    ],

    // Configuration des rapports
    'reporting' => [
        'enabled' => true,
        'auto_generate' => true,
        'include_charts' => true,
        'formats' => ['pdf', 'xlsx', 'html'],
        'retention_days' => 90,
        'email_reports' => env('IMPORT_EXPORT_EMAIL_REPORTS', false),
        'report_schedule' => 'weekly', // daily, weekly, monthly
    ],

    // Configuration du cache
    'cache' => [
        'enabled' => env('IMPORT_EXPORT_CACHE', true),
        'ttl' => env('IMPORT_EXPORT_CACHE_TTL', 3600), // 1 heure
        'prefix' => 'import_export',
        'tags' => ['import-export', 'files', 'mappings'],
        'store' => env('IMPORT_EXPORT_CACHE_STORE', 'redis'),
    ],

    // Configuration de performance
    'performance' => [
        'use_chunking' => true,
        'chunk_size' => 100,
        'use_transactions' => true,
        'optimize_queries' => true,
        'preload_relationships' => true,
        'use_bulk_operations' => true,
        'memory_monitoring' => true,
        'gc_collect_cycles' => true, // Garbage collection
    ],

    // Configuration des webhooks
    'webhooks' => [
        'enabled' => env('IMPORT_EXPORT_WEBHOOKS', false),
        'job_started' => env('IMPORT_EXPORT_WEBHOOK_STARTED', ''),
        'job_completed' => env('IMPORT_EXPORT_WEBHOOK_COMPLETED', ''),
        'job_failed' => env('IMPORT_EXPORT_WEBHOOK_FAILED', ''),
        'timeout' => 30,
        'retry_attempts' => 3,
        'signature_header' => 'X-ImportExport-Signature',
        'secret' => env('IMPORT_EXPORT_WEBHOOK_SECRET', ''),
    ],

    // Configuration des API externes
    'external_apis' => [
        'enabled' => false,
        'timeout' => 30,
        'max_retries' => 3,
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60,
        ],
    ],

    // Messages d'erreur personnalisés
    'error_messages' => [
        'file_too_large' => 'Le fichier est trop volumineux (maximum :max Mo)',
        'invalid_format' => 'Format de fichier non supporté: :format',
        'invalid_data_type' => 'Type de données non supporté: :type',
        'mapping_required' => 'Le mapping des champs est obligatoire',
        'validation_failed' => 'La validation des données a échoué',
        'processing_failed' => 'Erreur lors du traitement des données',
        'quota_exceeded' => 'Quota d\'import/export dépassé',
        'job_not_found' => 'Job d\'import/export introuvable',
        'permission_denied' => 'Permission insuffisante pour cette opération',
    ],

    // Configuration UI
    'ui' => [
        'theme' => 'default',
        'show_progress_bar' => true,
        'show_preview' => true,
        'preview_rows' => 5,
        'show_validation_details' => true,
        'auto_refresh_status' => true,
        'refresh_interval' => 5000, // millisecondes
        'max_error_display' => 10,
        'pagination_size' => 20,
    ],

    // Intégrations tierces
    'integrations' => [
        'google_sheets' => [
            'enabled' => false,
            'client_id' => env('GOOGLE_SHEETS_CLIENT_ID', ''),
            'client_secret' => env('GOOGLE_SHEETS_CLIENT_SECRET', ''),
        ],
        'dropbox' => [
            'enabled' => false,
            'app_key' => env('DROPBOX_APP_KEY', ''),
            'app_secret' => env('DROPBOX_APP_SECRET', ''),
        ],
        'ftp' => [
            'enabled' => false,
            'default_connection' => 'import_export_ftp',
        ],
    ],

];
