<?php

namespace App\Events;

use App\Models\Subscription;

class RenewedSubscriptionEvent extends BaseSubscriptionEvent implements ISubscriptionEvent
{
    public function __construct(Subscription $subscription)
    {
        $this->eventInfo = 'Renewed';
        parent::__construct($subscription);
    }
}
