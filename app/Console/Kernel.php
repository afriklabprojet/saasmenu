<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Core Laravel scheduled tasks
        $schedule->command('inspire')->hourly();

        // Système de monitoring temps réel
        $schedule->command('system:monitor')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(5)
                 ->onOneServer()
                 ->runInBackground();

        // Système de backup automatique
        $schedule->command('backup:create')
                 ->daily()
                 ->at('02:30')
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // Backup hebdomadaire complet
        $schedule->command('backup:create --verify')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // Nettoyage backups anciens
        $schedule->command('backup:manage clean --force')
                 ->weekly()
                 ->saturdays()
                 ->at('04:00')
                 ->onOneServer();

        // Tests performance production (hebdomadaire)
        $schedule->command('performance:test --type=basic --save')
                 ->weekly()
                 ->saturdays()
                 ->at('05:00')
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // Nettoyage notifications anciennes (quotidien)
        $schedule->command('notifications:manage clear')
                 ->daily()
                 ->at('06:00')
                 ->onOneServer();

        // Test système notifications (hebdomadaire)
        $schedule->command('notifications:manage test')
                 ->weekly()
                 ->mondays()
                 ->at('09:00')
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // RestroSaaS Addons Scheduled Tasks
        // =================================

        // Import/Export Processing
        $schedule->command('addons:process-imports')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(10)
                 ->onOneServer()
                 ->runInBackground();

        $schedule->command('addons:process-exports')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(10)
                 ->onOneServer()
                 ->runInBackground();

        $schedule->command('addons:cleanup-files')
                 ->daily()
                 ->at('02:00')
                 ->onOneServer();

        // Scheduled Jobs Processing
        $schedule->command('addons:process-scheduled-jobs')
                 ->everyMinute()
                 ->withoutOverlapping(5)
                 ->onOneServer()
                 ->runInBackground();

        // Firebase Notifications
        $schedule->command('addons:send-notification')
                 ->everyMinute()
                 ->withoutOverlapping(5)
                 ->onOneServer()
                 ->runInBackground();

        $schedule->command('addons:cleanup-devices')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->onOneServer();

        // Scheduled Notifications (promotional, reminders, etc.)
        $schedule->command('addons:process-scheduled-notifications')
                 ->hourly()
                 ->withoutOverlapping(30)
                 ->onOneServer()
                 ->runInBackground();

        // POS System Maintenance
        $schedule->command('addons:pos-daily-summary')
                 ->dailyAt('23:30')
                 ->onOneServer();

        $schedule->command('addons:pos-cleanup-sessions')
                 ->daily()
                 ->at('01:00')
                 ->onOneServer();

        // Loyalty Program Processing
        $schedule->command('addons:loyalty-tier-updates')
                 ->daily()
                 ->at('00:30')
                 ->onOneServer();

        $schedule->command('addons:loyalty-birthday-notifications')
                 ->dailyAt('09:00')
                 ->onOneServer();

        $schedule->command('addons:loyalty-expiry-reminders')
                 ->weekly()
                 ->mondays()
                 ->at('10:00')
                 ->onOneServer();

        // Table QR Analytics
        $schedule->command('addons:tableqr-analytics-update')
                 ->hourly()
                 ->withoutOverlapping(15)
                 ->onOneServer();

        $schedule->command('addons:tableqr-cleanup-inactive')
                 ->monthly()
                 ->at('04:00')
                 ->onOneServer();

        // System Health and Monitoring
        $schedule->command('addons:health-check')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping(5)
                 ->onOneServer();

        $schedule->command('addons:performance-metrics')
                 ->hourly()
                 ->withoutOverlapping(15)
                 ->onOneServer();

        // Database Maintenance
        $schedule->command('addons:cleanup-old-logs')
                 ->weekly()
                 ->saturdays()
                 ->at('05:00')
                 ->onOneServer();

        $schedule->command('addons:optimize-database')
                 ->monthly()
                 ->at('06:00')
                 ->onOneServer();

        // Backup and Archive
        $schedule->command('addons:backup-data')
                 ->daily()
                 ->at('04:30')
                 ->onOneServer();

        $schedule->command('addons:archive-old-data')
                 ->monthly()
                 ->at('07:00')
                 ->onOneServer();

        // ========================================
        // Advanced Monitoring & Logging Tasks
        // ========================================

        // Surveillance santé système (haute fréquence)
        $schedule->command('monitoring:health-check')
                 ->everyFiveMinutes()
                 ->withoutOverlapping(3)
                 ->onOneServer()
                 ->runInBackground();

        // Surveillance santé avec alertes (fréquence normale)
        $schedule->command('monitoring:health-check --alert')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping(5)
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // Nettoyage automatique des logs (quotidien)
        $schedule->command('monitoring:cleanup --days=30')
                 ->daily()
                 ->at('02:30')
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@restro-saas.com');

        // Nettoyage approfondi des logs (hebdomadaire)
        $schedule->command('monitoring:cleanup --days=7')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->onOneServer();

        // Réchauffement du cache système (matin)
        $schedule->command('cache:warmup')
                 ->dailyAt('06:00')
                 ->onOneServer()
                 ->runInBackground();

        // Statistiques de cache (toutes les heures)
        $schedule->command('cache:stats')
                 ->hourly()
                 ->withoutOverlapping(10)
                 ->onOneServer()
                 ->runInBackground();

        // Optimisation base de données (hebdomadaire)
        $schedule->command('db:optimize')
                 ->weekly()
                 ->sundays()
                 ->at('04:00')
                 ->onOneServer();

        // API Rate Limit Reset
        $schedule->command('addons:reset-rate-limits')
                 ->hourly()
                 ->withoutOverlapping(5)
                 ->onOneServer();

        // Queue Health Monitoring
        $schedule->command('queue:monitor default,import_export,notifications,pos_processing,loyalty_processing --max=100')
                 ->everyMinute()
                 ->withoutOverlapping(2);

        // Failed Jobs Cleanup
        $schedule->command('queue:prune-failed --hours=48')
                 ->daily()
                 ->at('03:30');

        // Cache Warming (for better performance)
        $schedule->command('addons:warm-cache')
                 ->hourly()
                 ->between('06:00', '22:00')
                 ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
