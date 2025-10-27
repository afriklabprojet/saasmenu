<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $customer;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customer.' . $this->customer->id),
        ];
    }
}
