<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        // RestroSaaS Addons Specialized Queues
        'import_export' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'import_export',
            'retry_after' => 600, // 10 minutes for large files
            'block_for' => 5,
            'after_commit' => false,
        ],

        'notifications' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'notifications',
            'retry_after' => 60,
            'block_for' => 2,
            'after_commit' => false,
        ],

        'pos_processing' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'pos_processing',
            'retry_after' => 30, // Fast processing for POS
            'block_for' => 1,
            'after_commit' => true, // Ensure transaction completion
        ],

        'loyalty_processing' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'loyalty_processing',
            'retry_after' => 120,
            'block_for' => 3,
            'after_commit' => false,
        ],

        'firebase_high_priority' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'firebase_high_priority',
            'retry_after' => 30,
            'block_for' => 1,
            'after_commit' => false,
        ],

        'paypal_webhooks' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'paypal_webhooks',
            'retry_after' => 180,
            'block_for' => 2,
            'after_commit' => false,
        ],

        'analytics' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'analytics',
            'retry_after' => 300,
            'block_for' => 10,
            'after_commit' => false,
        ],

        'tableqr_analytics' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'tableqr_analytics',
            'retry_after' => 60,
            'block_for' => 5,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | RestroSaaS Addons Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to RestroSaaS addon queue processing,
    | including priority queues, retry strategies, and timeout settings.
    |
    */

    'addons' => [

        // Queue Priority Configuration
        'priorities' => [
            'pos_processing' => 10,      // Highest priority - real-time POS operations
            'firebase_high_priority' => 9, // Critical notifications
            'notifications' => 8,         // Regular notifications
            'loyalty_processing' => 7,    // Loyalty transactions
            'paypal_webhooks' => 6,      // Payment processing
            'tableqr_analytics' => 5,    // QR scan tracking
            'import_export' => 4,        // Bulk operations
            'analytics' => 3,            // Analytics processing
            'default' => 1,              // Standard operations
        ],

        // Retry Configuration per Queue
        'retry_strategies' => [
            'pos_processing' => [
                'max_tries' => 3,
                'backoff' => [5, 15, 30], // seconds
            ],
            'notifications' => [
                'max_tries' => 5,
                'backoff' => [10, 30, 60, 120, 300],
            ],
            'import_export' => [
                'max_tries' => 3,
                'backoff' => [60, 300, 900], // 1min, 5min, 15min
            ],
            'loyalty_processing' => [
                'max_tries' => 3,
                'backoff' => [30, 120, 300],
            ],
            'firebase_high_priority' => [
                'max_tries' => 5,
                'backoff' => [5, 10, 20, 40, 80],
            ],
            'paypal_webhooks' => [
                'max_tries' => 5,
                'backoff' => [30, 60, 120, 240, 480],
            ],
            'tableqr_analytics' => [
                'max_tries' => 2,
                'backoff' => [60, 180],
            ],
            'analytics' => [
                'max_tries' => 2,
                'backoff' => [300, 900], // 5min, 15min
            ],
        ],

        // Queue Worker Configuration
        'workers' => [
            'pos_processing' => [
                'count' => env('POS_QUEUE_WORKERS', 2),
                'timeout' => 30,
                'memory' => 128,
                'sleep' => 1,
            ],
            'notifications' => [
                'count' => env('NOTIFICATION_QUEUE_WORKERS', 3),
                'timeout' => 60,
                'memory' => 128,
                'sleep' => 2,
            ],
            'import_export' => [
                'count' => env('IMPORT_EXPORT_QUEUE_WORKERS', 1),
                'timeout' => 600, // 10 minutes for large files
                'memory' => 512,
                'sleep' => 5,
            ],
            'loyalty_processing' => [
                'count' => env('LOYALTY_QUEUE_WORKERS', 2),
                'timeout' => 120,
                'memory' => 128,
                'sleep' => 3,
            ],
            'firebase_high_priority' => [
                'count' => env('FIREBASE_QUEUE_WORKERS', 2),
                'timeout' => 30,
                'memory' => 128,
                'sleep' => 1,
            ],
            'paypal_webhooks' => [
                'count' => env('PAYPAL_QUEUE_WORKERS', 1),
                'timeout' => 180,
                'memory' => 128,
                'sleep' => 2,
            ],
            'tableqr_analytics' => [
                'count' => env('TABLEQR_QUEUE_WORKERS', 1),
                'timeout' => 60,
                'memory' => 128,
                'sleep' => 5,
            ],
            'analytics' => [
                'count' => env('ANALYTICS_QUEUE_WORKERS', 1),
                'timeout' => 300,
                'memory' => 256,
                'sleep' => 10,
            ],
        ],

        // Queue Health Monitoring
        'monitoring' => [
            'max_jobs_per_queue' => env('QUEUE_MAX_JOBS', 1000),
            'alert_threshold' => env('QUEUE_ALERT_THRESHOLD', 100),
            'health_check_interval' => env('QUEUE_HEALTH_CHECK_INTERVAL', 60), // seconds
            'failed_job_retention' => env('FAILED_JOB_RETENTION_HOURS', 168), // 1 week
        ],

        // Rate Limiting
        'rate_limits' => [
            'pos_processing' => [
                'per_minute' => 1000,
                'per_hour' => 10000,
            ],
            'notifications' => [
                'per_minute' => 500,
                'per_hour' => 5000,
            ],
            'import_export' => [
                'per_minute' => 10,
                'per_hour' => 100,
            ],
            'loyalty_processing' => [
                'per_minute' => 200,
                'per_hour' => 2000,
            ],
        ],

    ],

];
