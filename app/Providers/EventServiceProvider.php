<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // WhatsApp Notifications Events
        \App\Events\OrderCreatedEvent::class => [
            \App\Listeners\SendWhatsAppOrderNotification::class,
        ],
        \App\Events\PaymentConfirmedEvent::class => [
            \App\Listeners\SendWhatsAppPaymentConfirmation::class,
        ],
        \App\Events\DeliveryStatusUpdatedEvent::class => [
            \App\Listeners\SendWhatsAppDeliveryUpdate::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
