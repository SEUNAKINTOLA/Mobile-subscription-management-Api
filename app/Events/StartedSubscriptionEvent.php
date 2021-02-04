<?php

namespace App\Events;

use App\Models\Subscription;

class StartedSubscriptionEvent extends BaseSubscriptionEvent implements ISubscriptionEvent
{
    public function __construct(Subscription $subscription)
    {
        $this->eventInfo = 'Started';
        parent::__construct($subscription);
    }
}
