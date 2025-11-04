<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeferredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $action, array $data = [])
    {
        $this->action = $action;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Executing deferred job', [
                'job_id' => $this->job->getJobId(),
                'queue' => $this->queue,
                'action' => $this->action,
                'data' => $this->data
            ]);

            // Exécuter l'action basée sur le type
            match($this->action) {
                'whatsapp_notification' => $this->handleWhatsAppNotification(),
                'email_notification' => $this->handleEmailNotification(),
                'analytics_tracking' => $this->handleAnalyticsTracking(),
                'cache_warming' => $this->handleCacheWarming(),
                default => Log::warning('Unknown deferred action', ['action' => $this->action])
            };

            Log::info('Deferred job completed successfully', [
                'job_id' => $this->job->getJobId(),
                'action' => $this->action
            ]);

        } catch (\Exception $e) {
            Log::error('Deferred job failed', [
                'job_id' => $this->job->getJobId(),
                'action' => $this->action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle WhatsApp notification
     */
    protected function handleWhatsAppNotification(): void
    {
        $orderId = $this->data['order_id'] ?? 'unknown';

        // Simulation envoi WhatsApp (normalement appel API)
        sleep(1);

        Log::info('WhatsApp notification sent', [
            'order_id' => $orderId,
            'execution_time' => '~1s'
        ]);
    }

    /**
     * Handle email notification
     */
    protected function handleEmailNotification(): void
    {
        $orderId = $this->data['order_id'] ?? 'unknown';

        // Simulation envoi email
        usleep(600000); // 0.6s

        Log::info('Email notification sent', [
            'order_id' => $orderId,
            'execution_time' => '~0.6s'
        ]);
    }

    /**
     * Handle analytics tracking
     */
    protected function handleAnalyticsTracking(): void
    {
        $orderId = $this->data['order_id'] ?? 'unknown';

        // Simulation analytics
        usleep(400000); // 0.4s

        Log::info('Analytics tracked', [
            'order_id' => $orderId,
            'metrics' => ['revenue', 'conversion', 'customer_behavior'],
            'execution_time' => '~0.4s'
        ]);
    }

    /**
     * Handle cache warming
     */
    protected function handleCacheWarming(): void
    {
        $vendorId = $this->data['vendor_id'] ?? 'unknown';

        // Simulation cache warming
        usleep(300000); // 0.3s

        Log::info('Cache warmed up', [
            'vendor_id' => $vendorId,
            'caches' => ['menu', 'popular_items', 'vendor_data'],
            'execution_time' => '~0.3s'
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Deferred job failed permanently', [
            'action' => $this->action,
            'data' => $this->data,
            'error' => $exception->getMessage()
        ]);
    }
}
