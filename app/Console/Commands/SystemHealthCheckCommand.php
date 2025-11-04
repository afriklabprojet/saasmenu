<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoringService;
use Carbon\Carbon;

/**
 * Commande de surveillance système automatique
 * Vérifie la santé du système et génère des alertes
 */
class SystemHealthCheckCommand extends Command
{
    protected $signature = 'monitoring:health-check {--alert : Send alerts if issues found}';
    protected $description = 'Check system health and generate monitoring reports';
    
    protected $monitoring;
    
    public function __construct(MonitoringService $monitoring)
    {
        parent::__construct();
        $this->monitoring = $monitoring;
    }
    
    public function handle()
    {
        $this->info('🔍 Starting system health check...');
        
        // Génération du rapport de santé
        $healthReport = $this->monitoring->getHealthReport();
        
        $this->displayHealthReport($healthReport);
        
        // Vérifier les alertes actives
        $alerts = $this->monitoring->getActiveAlerts();
        if (!empty($alerts)) {
            $this->displayAlerts($alerts);
        }
        
        // Envoyer des alertes si demandé
        if ($this->option('alert') && $healthReport['overall_status'] !== 'healthy') {
            $this->sendHealthAlerts($healthReport, $alerts);
        }
        
        $this->info("✅ Health check completed at " . Carbon::now()->format('Y-m-d H:i:s'));
        
        return Command::SUCCESS;
    }
    
    /**
     * Afficher le rapport de santé
     */
    private function displayHealthReport(array $report): void
    {
        $status = $report['overall_status'];
        $score = $report['score'];
        
        $statusIcon = match($status) {
            'healthy' => '🟢',
            'degraded' => '🟡',
            'critical' => '🔴',
            default => '⚪'
        };
        
        $this->line('');
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 SYSTEM HEALTH REPORT");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line('');
        $this->line("Overall Status: {$statusIcon} " . strtoupper($status));
        $this->line("Health Score: {$score}/100");
        $this->line("Last Check: " . $report['last_check']);
        $this->line("Active Alerts: " . $report['alerts_count']);
        $this->line('');
        
        // Détails par composant
        $this->info("📋 COMPONENT STATUS:");
        $this->line('');
        
        foreach ($report['components'] as $component => $details) {
            $componentIcon = match($details['status']) {
                'healthy' => '✅',
                'degraded' => '⚠️',
                'critical' => '❌',
                default => '❓'
            };
            
            $this->line(sprintf(
                "  %s %-15s %s %s",
                $componentIcon,
                ucfirst($component),
                str_pad($details['status'], 10),
                $details['message']
            ));
        }
    }
    
    /**
     * Afficher les alertes actives
     */
    private function displayAlerts(array $alerts): void
    {
        $this->line('');
        $this->warn("🚨 ACTIVE ALERTS (" . count($alerts) . "):");
        $this->line('');
        
        $criticalCount = 0;
        $highCount = 0;
        $mediumCount = 0;
        
        foreach ($alerts as $alert) {
            $severity = $alert['severity'] ?? 'medium';
            $alertType = $alert['alert_type'] ?? 'unknown';
            $timestamp = Carbon::parse($alert['timestamp'])->format('H:i:s');
            
            $severityIcon = match($severity) {
                'critical' => '🔴',
                'high' => '🟠',
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪'
            };
            
            switch ($severity) {
                case 'critical': $criticalCount++; break;
                case 'high': $highCount++; break;
                case 'medium': $mediumCount++; break;
            }
            
            $message = $alert['event'] ?? $alert['type'] ?? 'Unknown alert';
            $this->line("  {$severityIcon} [{$timestamp}] {$message} ({$alertType})");
        }
        
        $this->line('');
        $this->line("Summary: {$criticalCount} critical, {$highCount} high, {$mediumCount} medium priority alerts");
    }
    
    /**
     * Envoyer des alertes par email/notification
     */
    private function sendHealthAlerts(array $healthReport, array $alerts): void
    {
        $this->info('📧 Sending health alerts...');
        
        // Ici vous pouvez implémenter l'envoi d'emails, Slack, etc.
        $this->monitoring->recordSecurityEvent(
            'health_check_alert',
            'medium',
            [
                'overall_status' => $healthReport['overall_status'],
                'score' => $healthReport['score'],
                'alerts_count' => count($alerts),
                'critical_alerts' => array_filter($alerts, fn($a) => ($a['severity'] ?? '') === 'critical')
            ]
        );
        
        $this->info('✅ Health alerts sent successfully');
    }
}