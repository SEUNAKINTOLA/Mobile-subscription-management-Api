<?php

namespace App\Events;

use App\Models\Subscription;

interface ISubscriptionEvent
{
    function getSubscription(): Subscription;
    function getEventInfo(): string;
}