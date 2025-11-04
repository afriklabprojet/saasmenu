<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement de mise à jour des métriques dashboard
 */
class DashboardMetricUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $vendorId;
    public string $eventType;
    public array $metrics;
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(int $vendorId, string $eventType, array $metrics)
    {
        $this->vendorId = $vendorId;
        $this->eventType = $eventType;
        $this->metrics = $metrics;
        $this->timestamp = now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard.' . $this->vendorId),
            new Channel('dashboard.metrics.' . $this->vendorId)
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vendor_id' => $this->vendorId,
            'event_type' => $this->eventType,
            'metrics' => $this->metrics,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Get the name of the event.
     */
    public function broadcastAs(): string
    {
        return 'metric.updated';
    }
}