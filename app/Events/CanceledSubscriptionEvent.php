<?php

namespace App\Events;

use App\Models\Subscription;

class CanceledSubscriptionEvent extends BaseSubscriptionEvent implements ISubscriptionEvent
{
    public function __construct(Subscription $subscription)
    {
        $this->eventInfo = 'Canceled';
        parent::__construct($subscription);
    }
}
