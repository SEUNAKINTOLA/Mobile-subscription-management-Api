<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Exceptions\NotFoundException;
use App\Services\ThirdPartyService;
use App\Events\ISubscriptionEvent;
use Log;

class NotifySubscriptionStatusListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 5;
    public $delay = 5;
    public $afterCommit = true;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  ISubscriptionEvent  $event
     * @return void
     *
     * @throws NotFoundException
     */
    public function handle(ISubscriptionEvent $event)
    {
        $subscription   = $event->getSubscription();
        $device         = $subscription->getDevice();

        $thirdPartyService = new ThirdPartyService();
        $thirdPartyService->sendSubscriptionStatusEvent($device->getAppId(), $device->getId(), $event->getEventInfo());

        if (config('app.env') !== 'production')
        {
            Log::info(
                'Event listened: '.$event->getEventInfo(),
                [
                    'AppId' => $device->getAppId(),
                    'DeviceId' => $device->getId(),
                ]
            );
        }
    }
}
