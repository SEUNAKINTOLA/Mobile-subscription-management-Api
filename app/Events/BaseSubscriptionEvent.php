<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseSubscriptionEvent implements ISubscriptionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $subscription;
    protected $eventInfo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getEventInfo(): string
    {
        return $this->eventInfo;
    }
}