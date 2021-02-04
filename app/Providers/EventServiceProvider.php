<?php

namespace App\Providers;

use App\Events\CanceledSubscriptionEvent;
use App\Events\RenewedSubscriptionEvent;
use App\Events\StartedSubscriptionEvent;
use App\Listeners\NotifySubscriptionStatusListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        StartedSubscriptionEvent::class => [
            NotifySubscriptionStatusListener::class,
        ],
        RenewedSubscriptionEvent::class => [
            NotifySubscriptionStatusListener::class,
        ],
        CanceledSubscriptionEvent::class => [
            NotifySubscriptionStatusListener::class,
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
